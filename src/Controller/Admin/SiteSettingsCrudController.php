<?php

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteSettingsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiteSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ustawienia strony')
            ->setEntityLabelInPlural('Ustawienia strony');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new(
            'teacherName',
            'Imię nauczyciela'
        );

        yield TextareaField::new(
            'teacherDescription',
            'Opis'
        );

        yield ImageField::new(
            'teacherPhoto',
            'Zdjęcie'
        )
            ->setUploadDir('public/uploads/teacher')
            ->setBasePath('/uploads/teacher');

        yield TextField::new(
            'phone',
            'Telefon'
        );

        yield EmailField::new(
            'email',
            'E-mail'
        );
    }
}
