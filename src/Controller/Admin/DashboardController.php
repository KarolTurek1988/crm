<?php

namespace App\Controller\Admin;

use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\ContactHistoryCrudController;
use App\Controller\Admin\LeadCrudController;
use App\Controller\Admin\LeadImportErrorCrudController;
use App\Controller\Admin\SiteSettingsCrudController;
use App\Controller\Admin\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(
    routePath: '/admin',
    routeName: 'admin'
)]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render(
            '@EasyAdmin/page/content.html.twig',
            [
                'pageName' => 'CRM',
                'pageTitle' => 'CRM',
            ]
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CRM');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(
            'Dashboard',
            'fa fa-home'
        );

        yield MenuItem::section('CRM');

        yield MenuItem::linkTo(
            ClientCrudController::class,
            'Klienci',
            'fa fa-users'
        );

        yield MenuItem::linkTo(
            LeadCrudController::class,
            'Leady',
            'fa fa-filter'
        );

        yield MenuItem::linkTo(
            ContactHistoryCrudController::class,
            'Historia kontaktów',
            'fa fa-comments'
        );

        yield MenuItem::section('Meta');

        yield MenuItem::linkTo(
            LeadImportErrorCrudController::class,
            'Błędy importu',
            'fa fa-exclamation-triangle'
        );

        yield MenuItem::section('System');

        yield MenuItem::linkTo(
            UserCrudController::class,
            'Użytkownicy',
            'fa fa-user'
        );

        yield MenuItem::linkTo(
            SiteSettingsCrudController::class,
            'Ustawienia strony',
            'fa fa-cog'
        );
    }
}
