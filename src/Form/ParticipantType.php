<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('pseudo', TextType::class,['label'=> 'Pseudo : '])
            ->add('prenom', TextType::class,['label'=> 'Prénom : '])
            ->add('nom', TextType::class,['label'=> 'Nom : '])
            ->add('telephone', TextType::class, [
                'label'=> 'Téléphone : ',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Votre numéro ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Entrer un numéro valide, minimum {{ limit }} chiffres',
                        'max' => 15,
                    ]),
                ],
            ])
            ->add('email', TextType::class,['label'=> 'Email : '])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe : '],
                'second_options' => ['label' => 'Confirmer votre mot de passe : '],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Votre password ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre password doit faire {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('campus', EntityType::class, [
                'label'=> 'Choississez votre Campus : ',
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                 'mapped' => false,
            ])

            /*->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Participant' => 'ROLE_PARTICIPANT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Rôles',
                'data' => ['ROLE_PARTICIPANT'], // Valeur par défaut
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
