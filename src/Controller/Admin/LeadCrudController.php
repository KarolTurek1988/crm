<?php

namespace App\Controller\Admin;

use App\Entity\Lead;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LeadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Lead::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Lead')
            ->setEntityLabelInPlural('Leady')
            ->setSearchFields([
                'metaLeadId',
                'formId',
                'campaignId',
            ])
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

        yield TextField::new(
            'metaLeadId',
            'Meta Lead ID'
        );

        yield TextField::new(
            'source',
            'Źródło'
        );

        yield TextField::new(
            'formId',
            'Formularz'
        );

        yield TextField::new(
            'pageId',
            'Strona'
        );

        yield TextField::new(
            'adId',
            'Reklama'
        );

        yield TextField::new(
            'adSetId',
            'Ad Set'
        );

        yield TextField::new(
            'campaignId',
            'Kampania'
        );

        yield DateTimeField::new(
            'createdAt',
            'Utworzono'
        )->hideOnForm();

        yield DateTimeField::new(
            'importedAt',
            'Zaimportowano'
        )->hideOnForm();

        yield ArrayField::new(
            'payload',
            'Payload'
        )->hideOnIndex();
    }
}
