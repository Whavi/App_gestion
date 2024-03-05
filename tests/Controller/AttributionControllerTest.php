<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Faker\Factory;

class AttributionControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->login(); 
    }

    private function login()
    {
        $crawler = $this->client->request('GET', '/connexion');
        $form = $crawler->selectButton('Se connecter')->form([
            // ["ROLE_USER"]
            '_username' => 'test@test.org',
            '_password' => '123',
         
            // ["ROLE_USER","ROLE_ADMIN"]
            // '_username' => 'stage.it@secours-islamique.org',
            // '_password' => 'password',
          
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();
    }

    /* 
        
    
        TEST DE LA PAGE PRODUIT  
        
        
    */

    public function testProduitPage()
    {
        $this->client->request('GET', '/gestion');
        $this->assertResponseIsSuccessful();
    }
    public function testAddItemProduitEntry()
    {
        $this->client->request('GET', '/gestion/addItem');
        $this->assertResponseIsSuccessful();
    }
    public function testProduitSearch()
    {
        $crawler = $this->client->request('GET', '/gestion');
        $form = $crawler->selectButton('Rechercher')->form();
        // foreach ($form->all() as $field) {
        //     $name = $field->getName();
        //     echo $name . "\n";
        // }
        $form['search_type_product[ref]']->setValue('456');

        $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAddItemProduit()
    {
        $crawler = $this->client->request('GET', '/gestion/addItem'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'user_form_product[identifiant]' => '12345',
            'user_form_product[nom]' => 'Nom du modèle',
            'user_form_product[ref]' => 'Ref123',
            'user_form_product[category]' => 'Ordinateur Portable',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testProduitDelete()
    {
        $this->client->request('DELETE', '/gestion/delete/1');
        $this->assertResponseRedirects('/gestion');
    }

    public function testProduitEdit()
    {
        $crawler = $this->client->request('GET', '/gestion/edit/2'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'edit_form_product[identifiant]' => '76548915',
            'edit_form_product[nom]' => 'Lenovo',
            'edit_form_product[ref]' => 'Ref12345',
            'edit_form_product[category]' => 'Ordinateur Fixe',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }



    /* 
        

        TEST DE LA PAGE COLLABORATEUR  
        
        
    */
    public function testCollaborateurPage()
    {
        $this->client->request('GET', '/gestion/compte/collaborateur');
        $this->assertResponseIsSuccessful();
    }

    public function testCollaborateurSearch()
    {
        $crawler = $this->client->request('GET', '/gestion/compte/collaborateur');
        $form = $crawler->selectButton('Rechercher')->form();
        $form['search_type_collaborateur[nom]']->setValue('456');

        $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testCollaborateurEntry()
    {
        $this->client->request('GET', '/gestion/compte/collaborateur/addItem');
        $this->assertResponseIsSuccessful();
    }

    public function testCollaborateur()
    {
        $crawler = $this->client->request('GET', '/gestion/compte/collaborateur/addItem'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'user_form_collaborateur[nom]' => 'Dupont',
            'user_form_collaborateur[prenom]' => 'Gerard',
            'user_form_collaborateur[email]' => 'test@test.org',
            'user_form_collaborateur[departement]' => '5',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testCollaborateurDelete()
    {
        $this->client->request('DELETE', '/gestion/compte/collaborateur/delete/1');
        $this->assertResponseRedirects('/gestion/compte/collaborateur');
    }


    public function testCollaborateurEdit()
    {
        $crawler = $this->client->request('GET', '/gestion/compte/collaborateur/edit/2'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'edit_form_collaborateur[nom]' => 'Dupont',
            'edit_form_collaborateur[prenom]' => 'Gerard',
            'edit_form_collaborateur[email]' => 'test@test.com',
            'edit_form_collaborateur[departement]' => '5',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }


    /* 
        
    
        TEST DE LA PAGE UTILISATEUR  
        
        
    */

    public function testUserPage()
    {
        $this->client->request('GET', '/gestion/compte/utilisateur');
        $this->assertResponseIsSuccessful();
    }

    public function testUserSearch()
    {
        $crawler = $this->client->request('GET', '/gestion/compte/utilisateur');
        $form = $crawler->selectButton('Rechercher')->form();
        $form['search_type_user[nom]']->setValue('456');

        $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserEntry()
    {
        $this->client->request('GET', '/gestion/compte/utilisateur/addUser');
        $this->assertResponseIsSuccessful();
    }

    public function testUser()
    {
        $faker = Factory::create();
        $crawler = $this->client->request('GET', '/gestion/compte/utilisateur/addUser'); 
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'user_form_item[nom]' => 'Dupont',
            'user_form_item[prenom]' => 'Gerard',
            'user_form_item[email]' => $faker->email(),
            'user_form_item[password][first]' => 'test',
            'user_form_item[password][second]' => 'test',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testUserDelete()
    {
        $this->client->request('DELETE', '/gestion/compte/utilisateur/delete/1');
        $this->assertResponseRedirects('/gestion/compte/utilisateur');
    }

    public function testUserEdit()
    {
        $faker = Factory::create();
        $crawler = $this->client->request('GET', '/gestion/compte/utilisateur/edit/2'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'edit_form_user[nom]' => 'Dupont',
            'edit_form_user[prenom]' => 'Gerard',
            'edit_form_user[email]' => $faker->email(),
            'edit_form_user[password][first]' => 'test',
            'edit_form_user[password][second]' => 'test',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }


    
    /* 
        
    
        TEST DE LA PAGE DEPARTEMENT  
        
        
    */


    public function testDepartementPage()
    {
        $this->client->request('GET', '/gestion/departement');
        $this->assertResponseIsSuccessful();
    }

    public function testDepartementSearch()
    {
        $crawler = $this->client->request('GET', '/gestion/departement');
        $form = $crawler->selectButton('Rechercher')->form();
        $form['search_type_departement[nom]']->setValue('456');

        $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDepartementEntry()
    {
        $this->client->request('GET', '/gestion/departement/addDepartement');
        $this->assertResponseIsSuccessful();
    }

    public function testDepartement()
    {
        $crawler = $this->client->request('GET', '/gestion/departement/addDepartement'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
           'user_form_departement[nom]' => 'informatique',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testDepartementDelete()
    {
        $this->client->request('DELETE', '/gestion/departement/delete/1');
        $this->assertResponseRedirects('/gestion/departement');
    }

    public function testDepartementEdit()
    {
        $crawler = $this->client->request('GET', '/gestion/departement/edit/2'); 
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'edit_form_departement[nom]' => 'Informatique',
        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

      
    /* 
        
    
        TEST DE LA PAGE ATTRIBUTION  
        
        
    */

    public function testAttributionPage()
    {
        $this->client->request('GET', '/gestion/nouvellesAttributions/attribution/');
        $this->assertResponseIsSuccessful();
        
        $this->client->request('GET', '/gestion/anciennesAttributions/attribution/');
        $this->assertResponseIsSuccessful();
    }
    
    public function testAddItemAttributionEntry()
    {
        $this->client->request('GET', '/gestion/attribution/addAttribution');
        $this->assertResponseIsSuccessful();
    }

    public function testAddItemAttribution()
    {
        $faker = Factory::create();
        $crawler = $this->client->request('GET', '/gestion/attribution/addAttribution'); 
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'user_form_attribution[collaborateur]' => '2',
            'user_form_attribution[Product]' => '3',
            'user_form_attribution[dateAttribution]' => (new \DateTime())->format('Y-m-d'), 
            'user_form_attribution[dateRestitution]' => $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'user_form_attribution[descriptionProduct]' => 'test',
            'user_form_attribution[remarque]' => 'test',

        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testGestionAttributionDelete()
    {
        $this->client->request('DELETE', '/gestion/attribution/delete/1');
        $this->assertResponseRedirects('/gestion/nouvellesAttributions/attribution/');
    }

    public function testGestionAttributionSigner()
    {
        $this->client->request('GET', '/gestion/attribution/signer/2');
        $this->assertTrue($this->client->getResponse()->isRedirect('/gestion/nouvellesAttributions/attribution/'));
    }

    public function testGestionAttributionEditPage()
    {
        $faker = Factory::create();
        $crawler =  $this->client->request('GET', '/gestion/attribution/edit/2');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Submit')->form();
        $formData = [
            'edit_form_attribution[collaborateur]' => '2',
            'edit_form_attribution[Product]' => '3',
            'edit_form_attribution[dateAttribution]' => (new \DateTime())->format('Y-m-d'), 
            'edit_form_attribution[dateRestitution]' => $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'edit_form_attribution[descriptionProduct]' => 'test1',
            'edit_form_attribution[remarque]' => 'test1',

        ];
    
        $this->client->submit($form, $formData);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testGestionAttributionRendu()
    {
        $this->client->request('GET', '/gestion/attribution/rendu/2');
        $this->assertTrue($this->client->getResponse()->isRedirect('/gestion/nouvellesAttributions/attribution/'));
    }


    


    // public function testSendEmail()
    // {
    //     $this->client->request('GET', '/gestion/attribution/send-email/2');
    //     var_dump($this->client->getResponse());
    //     $this->assertResponseRedirects('/gestion/nouvellesAttributions/attribution/');
    // }


      
    /* 
        
    
        TEST DE LA PAGE LOG  
        
        
    */

    public function testLogPage()
    {
        $this->client->request('GET', '/log/entry');
        $this->assertResponseIsSuccessful();
    }


    // public function testSignature()
    // {
    //     $this->client->request('GET', '/gestion/attribution/signature/1');
    //     $this->assertResponseRedirects('/gestion/nouvellesAttributions/attribution/');
    // }
}
