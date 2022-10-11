<?php

namespace CaptJM\Bundle\StoryEntityBundle\Controller\Admin;

use CaptJM\Bundle\StoryEntityBundle\Entity\Story;
use CaptJM\Bundle\StoryEntityBundle\Tools\ChoiceGenerator;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StoryCrudController extends AbstractCrudController
{
    private ObjectManager $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new $entityFqcn();
        $entity->setPublishDate(new DateTime());
        $entity->setLocale($this->getParameter('app.default_locale'));

        return $entity;
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->getFields($pageName);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->showEntityActionsInlined()
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

    public function getFields(string $pageName, array $additionalFields = []): array
    {
        $fields = [
            IdField::new('id')
                ->hideOnForm(),
            ChoiceField::new('locale')
                ->setChoices(ChoiceGenerator::generate($this->getParameter('app.supported_locales'))),
            TextField::new('headline'),
            TextField::new('cover')
                ->setFormType(ElFinderType::class)
                ->setFormTypeOptions([
                    'instance' => 'form',
                    'enable' => true,
                ])
                ->onlyOnForms(),
            SlugField::new('slug')
                ->setTargetFieldName('headline'),
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
            DateTimeField::new('publishDate')
                ->renderAsChoice(),
        ];
        foreach ($additionalFields as $pos => $field) {
            array_splice($fields, $pos, 0, [$field]);
        }
        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        $translate = Action::new('translate', '', 'fa fa-language')
            ->setHtmlAttributes(['title' => 'Translate'])
            ->displayIf(function () {
                return $this->translatable();
            })
            ->linkToCrudAction('translate');
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $translate)
            // ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
            ;
    }

    public function translate(): RedirectResponse
    {
        $context = $this->getContext();
        /** @var Story $item */
        $item = $context->getEntity()->getInstance();
        $locales = explode('|', $this->getParameter('app.supported_locales'));
        if (count($locales) > 1) {
            $newL = null;
            foreach ($locales as $l) {
                if ($l !== $item->getLocale()) $newL = $l;
            }
            if ($newL) {
                $newItem = clone $item;
                $newItem->setLocale($newL)->setPublished(false);
                $this->em->persist($newItem);
                $this->em->flush();
            }
        }
        return $this->redirect($context->getReferrer());
    }

    private function translatable() :bool
    {
        $context = $this->getContext();
        $locales = explode('|', $this->getParameter('app.supported_locales'));
        if (count($locales) > 1) {
            /** @var Story $item */
            $item = $context->getEntity()->getInstance();
            $translation = $item->getTranslation();
            if (!$translation) {
                //$translation= new Tra
            }
            $newL = null;
            foreach ($locales as $l) {
                if ($l !== $item->getLocale()) $newL = $l;
            }
            if ($newL) {
                $newItem = clone $item;
                $newItem->setLocale($newL)->setPublished(false);
                $this->em->persist($newItem);
                $this->em->flush();
            }
        }
        return true;
    }
}
