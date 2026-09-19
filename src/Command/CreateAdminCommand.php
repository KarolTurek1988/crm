<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Tworzy konto administratora CRM.'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(
            <<<'HELP'
Komenda tworzy nowego użytkownika z rolą ROLE_ADMIN.

Przykład:

    php bin/console app:create-admin

Komenda poprosi o:
    - adres email
    - hasło
    - potwierdzenie hasła
HELP
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $helper = $this->getHelper('question');

        $output->writeln('');
        $output->writeln('<info>=== Tworzenie administratora CRM ===</info>');
        $output->writeln('');

        /*
         * EMAIL
         */
        $emailQuestion = new Question(
            'Email administratora: '
        );

        $emailQuestion->setValidator(
            function (?string $answer): string {
                $email = trim((string) $answer);

                if ($email === '') {
                    throw new \RuntimeException(
                        'Email nie może być pusty.'
                    );
                }

                $constraint = new Assert\Email();

                $violations = $this->validator->validate(
                    $email,
                    $constraint
                );

                if (count($violations) > 0) {
                    throw new \RuntimeException(
                        'Podany adres email jest nieprawidłowy.'
                    );
                }

                return $email;
            }
        );

        $emailQuestion->setMaxAttempts(3);

        $email = $helper->ask(
            $input,
            $output,
            $emailQuestion
        );

        $email = trim($email);

        /*
         * SPRAWDZENIE, CZY UŻYTKOWNIK JUŻ ISTNIEJE
         */
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email,
            ]);

        if ($existingUser !== null) {
            $output->writeln('');

            $output->writeln(
                '<error>Użytkownik o podanym adresie email już istnieje.</error>'
            );

            return Command::FAILURE;
        }

        /*
         * HASŁO
         */
        $passwordQuestion = new Question(
            'Hasło: '
        );

        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        $passwordQuestion->setValidator(
            function (?string $answer): string {
                $password = (string) $answer;

                if (strlen($password) < 8) {
                    throw new \RuntimeException(
                        'Hasło musi mieć co najmniej 8 znaków.'
                    );
                }

                return $password;
            }
        );

        $passwordQuestion->setMaxAttempts(3);

        $password = $helper->ask(
            $input,
            $output,
            $passwordQuestion
        );

        /*
         * POTWIERDZENIE HASŁA
         */
        $confirmationQuestion = new Question(
            'Powtórz hasło: '
        );

        $confirmationQuestion->setHidden(true);
        $confirmationQuestion->setHiddenFallback(false);

        $confirmationQuestion->setValidator(
            function (?string $answer) use ($password): string {
                if ((string) $answer !== $password) {
                    throw new \RuntimeException(
                        'Hasła nie są identyczne.'
                    );
                }

                return (string) $answer;
            }
        );

        $confirmationQuestion->setMaxAttempts(3);

        $helper->ask(
            $input,
            $output,
            $confirmationQuestion
        );

        /*
         * UTWORZENIE UŻYTKOWNIKA
         */
        $user = new User();

        $user->setEmail($email);

        $user->setRoles([
            'ROLE_ADMIN',
        ]);

        $user->setActive(true);

        /*
         * HASHOWANIE HASŁA
         */
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);

        /*
         * ZAPIS DO BAZY
         */
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        /*
         * INFORMACJA DLA UŻYTKOWNIKA
         */
        $output->writeln('');

        $output->writeln(
            '<info>Administrator został utworzony pomyślnie.</info>'
        );

        $output->writeln(
            sprintf(
                'Email: <comment>%s</comment>',
                $user->getEmail()
            )
        );

        $output->writeln(
            'Rola: <comment>ROLE_ADMIN</comment>'
        );

        $output->writeln(
            'Status: <comment>aktywny</comment>'
        );

        $output->writeln('');

        return Command::SUCCESS;
    }
}
