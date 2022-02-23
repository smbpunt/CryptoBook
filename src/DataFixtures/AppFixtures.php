<?php

namespace App\DataFixtures;

use App\Entity\Cryptocurrency;
use App\Entity\Position;
use App\Entity\User;
use App\Service\CryptocurrencyService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private CryptocurrencyService $cryptoService;
    private UserPasswordHasherInterface $hasher;

    /**
     * @param CryptocurrencyService $cryptoService
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(CryptocurrencyService $cryptoService, UserPasswordHasherInterface $hasher)
    {
        $this->cryptoService = $cryptoService;
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $btc = new Cryptocurrency();
        $btc->setLibelleCoingecko('bitcoin');
        $manager->persist($btc);

        $eth = new Cryptocurrency();
        $eth->setLibelleCoingecko('ethereum');
        $manager->persist($eth);

        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $user->setEmail($faker->email)
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setIsVerified(false);

            for ($p = 0; $p < random_int(1, 3); $p++) {
                $position = new Position($user);
                $position->setCoin($btc)
                    ->setNbCoins($faker->randomFloat(4, 0.01, 1))
                    ->setEntryCost($faker->randomFloat(2, 5000, 30000))
                    ->setOpenedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }


            for ($p = 0; $p < random_int(1, 3); $p++) {
                $position = new Position($user);
                $position->setCoin($eth)
                    ->setNbCoins($faker->randomFloat(2, 0.1, 10))
                    ->setEntryCost($faker->randomFloat(2, 500, 3000))
                    ->setOpenedAt(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months')));
                $manager->persist($position);
            }


            $manager->persist($user);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();

        $this->cryptoService->updateAllCryptos();
    }
}
