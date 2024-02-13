<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('duree', IntegerType::class, ['label' => 'Durée (min) : '])
            ->add('dateLimiteInscription', DateType::class, ['label' => "Date limite d'inscription :"])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => "Nombre de places :"])
            ->add('infosSortie',TextareaType::class, ['required' => false,
                                                                'label'=>'Description et infos :'])

        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            "csrf_protection" => "false",
            "allow_extra_fields" => true
        ]);
    }
}
