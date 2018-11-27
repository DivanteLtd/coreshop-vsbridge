<?php

namespace Tests\Functional\Controller;

use CoreShop2VueStorefrontBundle\Tests\Functional\ApiTestCase;

class CustomerControllerTest extends ApiTestCase
{
    /**
     * @test
     *
     * @group functional
     */
    public function itShouldCreateCustomer()
    {
        $email = 'test@tester.com';
        $password = 'SecretPassword';

        $client = $this->createCustomer($email, $password);

        $actualResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(200, $actualResponse['code']);

        $this->assertArrayHasKeys([
            'id',
            'group_id',
            'created_at',
            'updated_at',
            'email',
            'firstname',
            'lastname',
            'store_id',
            'website_id',
            'addresses',
            'disable_auto_group_change'
            ], $actualResponse['result']);
    }

    /**
     * @test
     *
     * @group functional
     */
    public function itShouldEditCustomer()
    {
        $customer = json_decode(file_get_contents(__DIR__ . '/../Fixtures/Response/customer.json'), true);

        $email = 'test1@tester.com';
        $password = 'SecretPassword';

        $this->createCustomer($email, $password);

        $authenticatedClient = $this->createAuthenticatedClient($email, $password);

        $customer = $customer['result'];
        $customer['email'] = $email;

        $authenticatedClient->request('POST', '/vsbridge/user/me', ['customer' => $customer]);

        $response = json_decode($authenticatedClient->getResponse()->getContent(), true);

        $this->assertSame(200, $response['code']);
    }

    /**
     * @test
     *
     * @group functional
     */
    public function itShouldGetProfile()
    {
        $this->markTestSkipped();

        $email = 'test2@tester.com';
        $password = 'SecretPassword';

        $this->createCustomer($email, $password);

        $authenticatedClient = $this->createAuthenticatedClient($email, $password);

        $authenticatedClient->request('GET', '/vsbridge/user/me');

        $response = \json_decode($authenticatedClient->getResponse()->getContent(), true);

        $this->assertSame(200, $response['code']);
        $responseResult = $response['result'];

        $this->assertArrayHasKeys([
            'id',
            'group_id',
            'created_at',
            'updated_at',
            'email',
            'firstname',
            'lastname',
            'store_id',
            'website_id',
            'addresses'
        ], $responseResult);

/*        $address = $responseResult['addresses'][0];

        $this->assertArrayHasKeys([
            'id',
            'customer_id',
            'region',
            'region_id',
            'country_id',
            'street',
            'telephone',
            'postcode',
            'city',
            'firstname',
            'lastname'
        ], $address);*/
    }
}
