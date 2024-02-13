<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,
                ['label'=>false,
                    'constraints' => [
                        new Regex([
                            'pattern' => '/^[a-zA-Z]+$/',
                            'message' => 'Le nom de la ville ne doit contenir que des lettres'
                        ])
                    ]
                ])
            ->add('codePostal', TextType::class,
                ['label'=>false,
                    'constraints' => [
                        new Regex([
                            'pattern' => '/^[0-9]{5}$/',
                            'message' => 'Le code postal ne doit contenir que 5 chiffres'
                        ])
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
