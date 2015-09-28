<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class IvrControllerTest extends TestCase
{
    public function testWelcomeResponse()
    {
        // When
        $response = $this->call('POST', route('welcome'));
        $welcomeString = $response->getContent();
        $welcomeResponse = new SimpleXMLElement($welcomeString);

        // Then
        $this->assertEquals(1, $welcomeResponse->children()->count());

        $gatherCommand = $welcomeResponse->Gather;
        $this->assertNotNull($gatherCommand);
        $this->assertEquals(1, $gatherCommand->children()->count());

        $this->assertEquals('1', $gatherCommand->attributes()['numDigits']);
        $this->assertEquals(
            route('menu-response', [], false), $gatherCommand->attributes()['action']
        );

        $this->assertNotNull($welcomeResponse->Gather->Play);
    }

    public function testMainMenuOptionOne()
    {
        // When
        $response = $this->call('POST', route('menu-response'), ['Digits' => 1]);
        $menuString = $response->getContent();
        $menuResponse = new SimpleXMLElement($menuString);

        // Then
        $this->assertEquals(3, $menuResponse->children()->count());
        $this->assertNotNull($menuResponse->Say);
        $this->assertEquals(2, $menuResponse->Say->count());
        $this->assertNotNull($menuResponse->Hangup);
    }

    public function testMainMenuOptionTwo()
    {
        // When
        $response = $this->call('POST', route('menu-response'), ['Digits' => 2]);
        $menuString = $response->getContent();
        $menuResponse = new SimpleXMLElement($menuString);

        // Then
        $this->assertNotNull($menuResponse->Gather);
        $this->assertNotNull($menuResponse->Gather->Say);

        $this->assertEquals(1, $menuResponse->Gather->children()->count());
        $this->assertEquals(1, $menuResponse->children()->count());

        $this->assertEquals('1', $menuResponse->Gather->attributes()['numDigits']);
        $this->assertEquals(
            route('planet-connection', [], false),
            $menuResponse->Gather->attributes()['action']
        );
    }

    public function testNonexistentOption()
    {
        $response = $this->call('POST', route('menu-response'), ['Digits' => 99]);
        $errorResponse = new SimpleXMLElement($response->getContent());

        // Then
        $this->assertEquals('Returning to the main menu', $errorResponse->Say);
        $this->assertEquals(route('welcome', [], false), $errorResponse->Redirect);
    }

    public function testStarReturnToMenu()
    {
        $this->call('POST', route('menu-response'), ['Digits' => '*']);
        $this->assertRedirectedToRoute('welcome');

        $this->call('POST', route('planet-connection'), ['Digits' => '*']);
        $this->assertRedirectedToRoute('welcome');
    }
}
