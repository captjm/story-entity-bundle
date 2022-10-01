<?php

namespace App\Controller\Admin;

use App\Entity\Story;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class StoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setPublishDate(new \DateTime());

        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('headline'),
            TextField::new('cover')
                ->setFormType(ElFinderType::class)
                ->setFormTypeOptions([
                    'instance' => 'form',
                    'enable' => true,
                ])
                ->onlyOnForms(),
            TextField::new('cover', 'Cover Image')
                ->setTemplateName('crud/field/image')
                ->hideOnForm(),
            TextareaField::new('extract')
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('config_name', 'extract_config')
                ->onlyOnForms(),
            TextareaField::new('content')
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('config_name', 'content_config')
                ->onlyOnForms(),
            BooleanField::new('published'),
            DateTimeField::new('publishDate'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->addFormTheme('@ELFinderWidget/form/elfinder_widget.html.twig')
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets
            ->addJsFile('/bundles/fosckeditor/ckeditor.js')
            ->addJsFile('/bundles/elfinderwidget/ELFinderWidget.js')
        ;
        return $assets;
    }
}
