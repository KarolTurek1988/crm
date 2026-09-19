<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Użytkownik')
            ->setEntityLabelInPlural('Użytkownicy');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield EmailField::new(
            'email',
            'E-mail'
        );

        yield TextField::new('password')
            ->setFormTypeOption('attr', [
                'autocomplete' => 'new-password',
            ])
            ->onlyOnForms();

        yield ChoiceField::new(
            'roles',
            'Role'
        )->allowMultipleChoices()
            ->setChoices([
                'Administrator' => 'ROLE_ADMIN',
                'Użytkownik' => 'ROLE_USER',
            ]);

        yield BooleanField::new(
            'active',
            'Aktywny'
        );

        yield DateTimeField::new(
            'createdAt',
            'Utworzono'
        )->hideOnForm();
    }
}
