<?php

namespace App\Controller\Admin;

use App\Entity\Trainings;
use App\Entity\Users;
use App\Repository\TrainingsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class ImportExportController extends AbstractController
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
        private readonly TrainingsRepository $trainingsRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/import-export', name: 'admin_import_export', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/import_export.html.twig');
    }

    #[Route('/import/students', name: 'admin_import_students', methods: ['POST'])]
    public function importStudents(Request $request): RedirectResponse
    {
        $backUrl = $request->headers->get('referer') ?: $this->generateUrl('admin');

        $file = $request->files->get('students_file');

        if (!$file) {
            $this->addFlash('danger', 'Aucun fichier envoyé');

            return $this->redirect($backUrl);
        }

        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            $this->addFlash('danger', 'Impossible de lire le fichier');

            return $this->redirect($backUrl);
        }

        $delimiter = ';';
        $headers = fgetcsv($handle, 0, $delimiter);

        $imported = 0;
        $updated = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            $data = array_combine($headers, $row);

            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $email = trim($data['email'] ?? '');

            if ($firstName === '' || $lastName === '') {
                continue;
            }

            $username = strtolower($firstName . '.' . $lastName);
            $username = preg_replace('/[^a-z0-9.]/', '', $username);

            $user = null;

            if ($email !== '') {
                $user = $this->usersRepository->findOneBy(['email' => $email]);
            }

            if (!$user) {
                $user = $this->usersRepository->findOneBy(['username' => $username]);
            }

            $isNew = false;

            if (!$user) {
                $user = new Users();
                $user->setCreatedAt(new \DateTimeImmutable());

                $password = bin2hex(random_bytes(8));
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $isNew = true;
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email ?: null);
            $user->setUsername($username);
            $user->setRoles(['ROLE_STUDENT']);
            $user->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);

            if ($isNew) {
                $imported++;
            } else {
                $updated++;
            }
        }

        fclose($handle);
        $this->entityManager->flush();

        $this->addFlash('success', "$imported créés, $updated mis à jour");

        return $this->redirect($backUrl);
    }

    #[Route('/import/trainings', name: 'admin_import_trainings', methods: ['POST'])]
    public function importTrainings(Request $request): RedirectResponse
    {
        $backUrl = $request->headers->get('referer') ?: $this->generateUrl('admin');

        $file = $request->files->get('trainings_file');
        if (!$file instanceof UploadedFile) {
            $this->addFlash('danger', 'Aucun fichier CSV formations selectionne.');

            return $this->redirect($backUrl);
        }

        $rows = $this->readCsvRows($file);
        if ([] === $rows) {
            $this->addFlash('danger', 'Le fichier CSV formations est vide ou invalide.');

            return $this->redirect($backUrl);
        }

        $headers = array_map([$this, 'normalizeHeader'], array_shift($rows));
        $imported = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $data = $this->combineRow($headers, $row);
            if ([] === $data || empty($data['name'])) {
                continue;
            }

            $training = $this->trainingsRepository->findOneBy(['name' => (string) $data['name']]);
            $isNew = false;

            if (!$training instanceof Trainings) {
                $training = new Trainings();
                $training->setName((string) $data['name']);
                $isNew = true;
            }

            $this->entityManager->persist($training);

            if ($isNew) {
                ++$imported;
            } else {
                ++$updated;
            }
        }

        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Import formations terminé ! %d créée(s), %d mise(s) à jour.', $imported, $updated));

        return $this->redirect($backUrl);
    }

    private function readCsvRows(UploadedFile $file): array
    {
        $path = $file->getPathname();

        if (!is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if (false === $handle) {
            return [];
        }

        $delimiter = ',';
        $foundNonEmptyLine = false;

        // Detect delimiter from the first non-empty physical line, without loading the whole file.
        while (false !== ($line = fgets($handle))) {
            if ('' === trim($line)) {
                continue;
            }

            $foundNonEmptyLine = true;
            $delimiter = str_contains($line, ';') ? ';' : ',';
            break;
        }

        if (false === $foundNonEmptyLine) {
            fclose($handle);
            return [];
        }

        // Rewind so that the header line is processed by fgetcsv as well.
        rewind($handle);

        $rows = [];

        while (false !== ($data = fgetcsv($handle, 0, $delimiter))) {
            // fgetcsv may return [null] for completely empty lines.
            if ($data === null || $data === [null]) {
                continue;
            }

            // Trim string values, preserving non-string types as-is.
            $trimmedRow = array_map(
                static function ($value) {
                    return is_string($value) ? trim($value) : $value;
                },
                $data
            );

            // Skip rows that are effectively empty after trimming.
            $allEmpty = true;
            foreach ($trimmedRow as $value) {
                if ($value !== '' && $value !== null) {
                    $allEmpty = false;
                    break;
                }
            }

            if ($allEmpty) {
                continue;
            }

            $rows[] = $trimmedRow;
        }

        fclose($handle);
        return $rows;
    }

    private function normalizeHeader(string $header): string
    {
        return strtolower(trim($header));
    }

    private function combineRow(array $headers, array $row): array
    {
        $combined = [];

        foreach ($headers as $index => $header) {
            if ('' === $header) {
                continue;
            }

            $combined[$header] = $row[$index] ?? null;
        }

        return $combined;
    }

    private function generateUsernameFromNames(string $firstName, string $lastName): string
    {
        $base = sprintf('%s.%s', $firstName, $lastName);
        $base = strtolower(trim($base));

        if (function_exists('transliterator_transliterate')) {
            $base = transliterator_transliterate('Any-Latin; Latin-ASCII', $base);
        }

        $base = preg_replace('/[^a-z0-9]+/', '.', $base) ?? '';
        $base = trim($base, '.');

        return '' !== $base ? $base : 'utilisateur';
    }

}
