<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use AppBundle\Service\CoreService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends AbstractType
{
    /**
     * @var CoreService
     */
    private $coreService;

    /**
     * TagType constructor.
     *
     * @param CoreService $coreService
     */
    public function __construct(CoreService $coreService)
    {
        $this->coreService = $coreService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'required' => false,
                'attr' => [
                    'class' => 'wysiwyg',
                ],
            ])
            ->add('sort', NumberType::class, [
                'label' => 'sort',
                'required' => false,
            ])
        ;

        if (!$options['data']->getEntries()->isEmpty()) {
            $builder->add('previewEntry', EntityType::class, [
                'label' => 'previewEntry',
                'required' => false,
                'class' => 'AppBundle:Entry',
                'choices' => $options['data']->getEntries(),
                'placeholder' => '',
                'attr' => [
                    'class' => 'select2 form-control',
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $object = $form->getData();

            $object->setDescription($this->coreService->purifyString($object->getDescription()));
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
