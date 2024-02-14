<?php

namespace App\Form;

use App\Entity\DocumentStorage;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Compiler\CheckArgumentsValidityPass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titulek'
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Veřejný',
                'required' => false
            ])
            ->add('documents', FileType::class, [
                'label' => 'Soubory',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DocumentStorage::class,
        ]);
    }
}
