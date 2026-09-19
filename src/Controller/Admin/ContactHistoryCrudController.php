<?php

namespace App\Controller\Admin;

use App\Entity\ContactHistory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactHistoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactHistory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kontakt')
            ->setEntityLabelInPlural('Historia kontaktów')
            ->setDefaultSort([
                'createdAt' => 'DESC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield AssociationField::new(
            'client',
            'Klient'
        );

        yield AssociationField::new(
            'user',
            'Użytkownik'
        );

        yield ChoiceField::new(
            'type',
            'Typ'
        )->setChoices([
            'Telefon' => ContactHistory::TYPE_PHONE,
            'E-mail' => ContactHistory::TYPE_EMAIL,
            'Spotkanie' => ContactHistory::TYPE_MEETING,
            'SMS' => ContactHistory::TYPE_SMS,
            'Inne' => ContactHistory::TYPE_OTHER,
        ]);

        yield TextField::new(
            'subject',
            'Temat'
        );

        yield TextareaField::new(
            'note',
            'Notatka'
        );

        yield DateTimeField::new(
            'createdAt',
            'Data'
        )->hideOnForm();
    }
}
