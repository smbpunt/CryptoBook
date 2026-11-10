<?php

namespace App\Tests\Loan\Controller;

use App\Loan\Entity\Loan;
use App\Tests\Fixtures\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LoanControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private Dataset $data;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->data = Dataset::load($this->em());
        $this->client->loginUser($this->data->user);
    }

    public function testXhrPostRendersTheDappsOfTheBlockchainWithoutSaving(): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $form = $crawler->selectButton('Ajouter')->form();
        $values = $form->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['nbCoins'] = '100';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $crawler = $this->client->xmlHttpRequest('POST', '/loan/new', $values);

        self::assertSame(1, $crawler->filter('#loan_dapp option[value="'.$this->data->aave->getId().'"]')->count());
        self::assertSame(1, $this->countLoans());
    }

    public function testCreateLoan(): void
    {
        $crawler = $this->client->request('GET', '/loan/new');
        $values = $crawler->selectButton('Ajouter')->form()->getPhpValues();
        $values['loan']['blockchain'] = (string) $this->data->ethereum->getId();
        $values['loan']['dapp'] = (string) $this->data->aave->getId();
        $values['loan']['nbCoins'] = '100';
        $values['loan']['coin'] = (string) $this->data->usdc->getId();

        $this->client->request('POST', '/loan/new', $values);

        self::assertResponseRedirects('/loan/');
        self::assertSame(2, $this->countLoans());
    }

    public function testIndexShowsTheTotal(): void
    {
        $this->client->request('GET', '/loan/');

        self::assertSelectorTextContains('main', 'Emprunt total : 500 $');
    }

    private function countLoans(): int
    {
        return $this->em()->getRepository(Loan::class)->count(['owner' => $this->data->user]);
    }

    private function em(): EntityManagerInterface
    {
        return self::getContainer()->get(EntityManagerInterface::class);
    }
}
