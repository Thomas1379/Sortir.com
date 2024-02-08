<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : '])
            ->add('dateHeureDebut', DateTimeType::class, ['label' => 'Date et heure de la sortie : '])
            ->add('duree', TimeType::class, ['label' => 'Durée : '])
            ->add('dateLimiteInscription', DateTimeType::class, ['label'=> "Date limite d'inscription :"])
            ->add('nbInscriptionsMax', IntegerType::class, ['label'=> "Nombre de places :"] )
            ->add('infosSortie') #TextareaType::class, ['label'=>'Description et infos :'] - la saisie devient obligatoire avec ce morceau de code (VQ)
            ->add('Lieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'Ville : ',
                'choice_label' => function (Lieu $lieu): string {
                    return $lieu->getVille()->getNom();
                },
                'placeholder' => 'Choisissez une option',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
