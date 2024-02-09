<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : '])
            ->add('dateHeureDebut', DateTimeType::class, ['label' => 'Date et heure de la sortie : '])
            ->add('duree', IntegerType::class, ['label' => 'Durée : '])
            ->add('dateLimiteInscription', DateTimeType::class, ['label' => "Date limite d'inscription :"])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => "Nombre de places :"])
            ->add('infosSortie') #TextareaType::class, ['label'=>'Description et infos :'] - la saisie devient obligatoire avec ce morceau de code (VQ)
            //le code ci-dessous est à revoir (VQ)
            ->add('Lieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'Ville : ',
                'choice_label' => function (Lieu $lieu): string {
                    return $lieu->getNom() .'-'. $lieu->getVille()->getNom();
                    },
                'placeholder' => 'Choisissez une option',
            ])
    //le code ci-dessous est la base pour faire une belle liste déroulante (VQ)
//            ->add('lieu', ChoiceType::class, [
//                    'choices' => [
//                        'Ville1' => [
//                            'lieu1' => 'lieu1',
//                            'lieu2' => 'lieu2',
//                        ],
//                        'Ville2' => [
//                            'lieu3' => 'lieu3',
//                            'lieu4' => 'lieu4',
//                        ],
//                    ],
//                     'placeholder' => 'Choisissez une lieu',
//                ]
//            )

        ;

    }




    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
