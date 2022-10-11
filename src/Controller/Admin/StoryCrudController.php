<?php

namespace CaptJM\Bundle\StoryEntityBundle\Controller\Admin;

use CaptJM\Bundle\StoryEntityBundle\Entity\Story;
use CaptJM\Bundle\StoryEntityBundle\Entity\Translation;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class StoryCrudController extends AbstractCrudController
{
    private ObjectManager $em;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(ManagerRegistry $doctrine, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->em = $doctrine->getManager();
        $this->adminUrlGenerator = $adminUrlGenerator;
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
                ->setDisabled(true),
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
        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
        $locales = explode('|', $this->getParameter('app.supported_locales'));
        if (count($locales) > 1) {
            $em = $this->em;
            foreach ($locales as $locale) {
                $url = $this->adminUrlGenerator
                    ->setController(get_class($this))
                    ->setAction('translate')
                    ->setEntityId(Request::createFromGlobals()->get('entityId'))
                    ->set('targetLocale', $locale)
                    ->generateUrl();
                $actions->add(Crud::PAGE_EDIT,
                    Action::new('transTo' . $locale, strtoupper($locale), 'fa')
                        ->setHtmlAttributes(['title' => 'Translate to ' . strtoupper($locale)])
                        ->displayIf(static function (Story $story) use ($locale, $em) {
                            $display = $story->getLocale() !== $locale;
                            if ($display) {
                                $translation = $story->getTranslation();
                                if ($translation) {
                                    $items = $em
                                        ->getRepository(get_class($story))
                                        ->findBy([
                                            'translation' => $translation
                                        ]);
                                    foreach ($items as $item) {
                                        if ($item->getLocale() === $locale) return false;
                                    }
                                }
                            }
                            return $display;
                        })
                        ->linkToUrl($url)
                );
            }
        }
        return $actions;
    }

    public function translate(AdminContext $adminContext): RedirectResponse
    {
        /** @var Story $entity */
        $entity = $adminContext->getEntity()->getInstance();
        $translation = $entity->getTranslation();
        if ($translation === null) {
            $translation = new Translation();
            $this->em->persist($translation);
            $entity->setTranslation($translation);
            $this->em->persist($entity);
        }
        $newEntity = clone $entity;
        $newEntity
            ->setId(null)
            ->setLocale($adminContext->getRequest()->get('targetLocale'))
            ->setTranslation($translation);
        $this->em->persist($newEntity);
        $this->em->flush();
        return $this->redirect(
            $this
                ->adminUrlGenerator
                ->setController(get_class($this))
                ->setAction(Action::EDIT)
                ->setEntityId($newEntity->getId())
                ->generateUrl()
        );
    }
}
