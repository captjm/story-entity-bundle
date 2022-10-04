<?php

namespace CaptJM\Bundle\StoryEntityBundle\Controller\Admin;

use CaptJM\Bundle\StoryEntityBundle\Entity\Story;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
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
    private array $fields = [];

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
        $fields = [
            IdField::new('id')
                ->hideOnForm()
                ->setCustomOption('weight', 10),
            TextField::new('headline')
                ->setCustomOption('weight', 20),
            TextField::new('cover')
                ->setFormType(ElFinderType::class)
                ->setFormTypeOptions([
                    'instance' => 'form',
                    'enable' => true,
                ])
                ->onlyOnForms()
                ->setCustomOption('weight', 30),
            TextField::new('cover', 'Cover Image')
                ->setTemplateName('crud/field/image')
                ->hideOnForm()
                ->setCustomOption('weight', 40),
            TextareaField::new('extract')
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('config_name', 'extract_config')
                ->onlyOnForms()
                ->setCustomOption('weight', 50),
            TextareaField::new('content')
                ->setFormType(CKEditorType::class)
                ->setFormTypeOption('config_name', 'content_config')
                ->onlyOnForms()
                ->setCustomOption('weight', 60),
            BooleanField::new('published')
                ->setCustomOption('weight', 70),
            DateTimeField::new('publishDate')
                ->setCustomOption('weight', 80),
        ];
        foreach ($this->fields as $f) {
            if ($f['pageName'] === $pageName) {
                array_splice($fields, $f['pos'], 0, [$f['field']]);
            }
        }
        return $fields;
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
            ->addJsFile('/bundles/elfinderwidget/ELFinderWidget.js');
        return $assets;
    }

    public function insertFieldAt(FieldInterface $field, int $pos, string $pageName): self
    {
        $this->fields [] = compact($field, $pos, $pageName);
        return $this;
    }
}
