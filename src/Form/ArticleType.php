<?php

namespace App\Form;

use App\Entity\Article;
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

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titulek'
            ])
            ->add('link', TextType::class, [
                'label' => 'Odkaz'
            ])
            ->add('content', TextType::class, [
                'label' => 'Obsah',
                'required' => false,
                'attr' => [
                    'rows' => 6
                ]
            ])
            ->add('mainPhoto', FileType::class, [
                'label' => 'Náhledová fotografie',
                'required' => false,
                'mapped' => false
            ])
            ->add('articlePhotos', FileType::class, [
                'label' => 'Fotografie',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
