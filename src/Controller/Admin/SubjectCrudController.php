<?php

namespace App\Controller\Admin;

use App\Entity\Subjects;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SubjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subjects::class;
    }

}
