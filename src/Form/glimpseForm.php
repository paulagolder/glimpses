<?php

// src/Forms/ContentFormType.php
namespace App\Form;

use App\Entity\Glimpse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class glimpseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder    ->add('language', ChoiceType::class, array(
        'choices'  => array(
         'fr' => 'fr',
         'de' => 'de',
         'en' => 'en', ),
            ));
        $builder->add('title', TextType::class, array('attr' => array('style' => 'width: 400px'),));
        $builder->add('text', TextareaType::class, array('attr' => array('style' => 'width: 400px ;height:400px;'),));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Glimpse::class,
        ));
    }
}
