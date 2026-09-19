<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Klient')
            ->setEntityLabelInPlural('Klienci')
            ->setSearchFields([
                'fullName',
                'email',
                'phone',
                'company',
            ])
            ->setDefaultSort([
                'createdAt' => 'DESC',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new(
            'fullName',
            'Imię i nazwisko'
        );

        yield TextField::new(
            'email',
            'E-mail'
        );

        yield TextField::new(
            'phone',
            'Telefon'
        );

        yield TextField::new(
            'company',
            'Firma'
        );

        yield ChoiceField::new(
            'status',
            'Status'
        )->setChoices([
            'Nowy' => Client::STATUS_NEW,
            'Skontaktowany' => Client::STATUS_CONTACTED,
            'Oferta wysłana' => Client::STATUS_OFFER_SENT,
            'Wygrany' => Client::STATUS_WON,
            'Utracony' => Client::STATUS_LOST,
        ]);

        yield TextEditorField::new(
            'notes',
            'Notatki'
        );

        yield DateTimeField::new(
            'createdAt',
            'Utworzono'
        )->hideOnForm();
    }
}
