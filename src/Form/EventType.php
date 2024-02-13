<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Název akce'
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Datum a čas konání',
                'widget' => 'single_text'
            ])
            ->add('schedule', TextType::class, [
                'label' => 'Program',
                'required' => false,
                'attr' => [
                    'rows' => 6
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Typ akce',
                'choices' => [
                    'Veřejná' => 'public',
                    'Soukromá' => 'private'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Fotografie',
                'required' => false,
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
