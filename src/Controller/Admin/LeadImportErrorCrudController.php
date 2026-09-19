<?php

namespace App\Controller\Admin;

use App\Entity\LeadImportError;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LeadImportErrorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LeadImportError::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Błąd importu')
            ->setEntityLabelInPlural('Błędy importu')
            ->setDefaultSort([
                'createdAt' => 'DESC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new(
            'metaLeadId',
            'Meta Lead ID'
        );

        yield TextareaField::new(
            'error',
            'Błąd'
        );

        yield ArrayField::new(
            'payload',
            'Payload'
        );

        yield DateTimeField::new(
            'createdAt',
            'Data'
        )->hideOnForm();
    }
}
