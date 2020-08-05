<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, ['label' =>' Prénom :'])
            ->add('lastname', TextType::class, ['label' => 'Nom :'])
            ->add('category', ChoiceType::class, [
                'label' => 'Categorie :',
                'choices'  => [
                'coach' => 'coach',
                'heros' => 'hero',
                ],
            ])
            ->add('description', TextareaType::class, ['label' => 'Description :'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class
        ]);
    }
}
