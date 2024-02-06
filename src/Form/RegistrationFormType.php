<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Repository\CampusRepository;
use PhpParser\Node\Expr\New_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class)
            ->add('plainPassword', PasswordType::class, [
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
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
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('telephone', TextType::class)
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Participant' => 'ROLE_PARTICIPANT',
                    'Organisateur' => 'ROLE_ORGANISATEUR',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Rôles',
                'data' => ['ROLE_PARTICIPANT'], // Valeur par défaut
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
