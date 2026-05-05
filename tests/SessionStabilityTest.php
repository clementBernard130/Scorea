<?php

namespace App\Tests\Functional;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SessionStabilityTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRepeatedClassNavigationMaintainsSession(): void
    {
        // Get a teacher user from fixtures
        $userRepository = static::getContainer()->get(UsersRepository::class);
        $teacher = $userRepository->findOneBy(['username' => 'teacher1']);
        
        $this->assertNotNull($teacher, 'Teacher user should exist from fixtures');

        // Login the user using Symfony's test helper
        $this->client->loginUser($teacher);

        // Make several requests to /classes/1 to ensure session is maintained
        for ($i = 0; $i < 5; $i++) {
            $this->client->request('GET', '/classes/1');

            $statusCode = $this->client->getResponse()->getStatusCode();
            
            // Should not redirect (302) - which would indicate lost session/authentication
            $this->assertNotEquals(
                302,
                $statusCode,
                "Request #" . ($i + 1) . " to /classes/1 should not redirect (session should be valid)"
            );

            // Status should be 200 (success) or 403/404, but not a redirect to login
                $this->assertContains(
                $statusCode,
                [200, 403, 404],
                "Request #" . ($i + 1) . " should be 200, 403, or 404, not " . $statusCode
            );
        }
    }

    
    public function testSessionConfigurationIsCorrect(): void
    {
        // Check PHP session settings
        $this->assertEquals(1800, ini_get('session.gc_maxlifetime'), 'PHP session.gc_maxlifetime should be 1800 (30 min)');
        $this->assertEquals(1800, ini_get('session.cookie_lifetime'), 'PHP session.cookie_lifetime should be 1800 (30 min)');
    }
}
