<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Services_Twilio_Twiml;

class IvrController extends Controller
{
    private $_thankYouMessage = 'Thank you for calling the ET Phone Home' +
                             ' Service - the adventurous alien\'s first choice' +
                             ' in intergalactic travel.';
    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function showWelcome()
    {
        $response = new Services_Twilio_Twiml;
        $gather = $response->gather(
            ['numDigits' => 1,
             'action' => route('menu-response', [], false)]
        );

        $gather->play(
            'http://howtodocs.s3.amazonaws.com/et-phone.mp3',
            ['loop' => 3]
        );

        return $response;
    }

    /**
     * Responds to selection of an option by the caller
     *
     * @return \Illuminate\Http\Response
     */
    public function showMenuResponse(Request $request)
    {
        $optionActions = [
            '1' => $this->_getReturnInstructions()
        ];
        $selectedOption = $request->input('Digits');

        $actionExists = isset($optionActions[$selectedOption]);

        // Explicit boolean test to avoid automatic casting
        if ($actionExists) {
            $selectedAction = $optionActions[$selectedOption];
            return $selectedAction;

        } elseif ($actionExists) {
            $response = new Services_Twilio_Twiml;
            $response->say('That is not a valid option in the menu');
            $response->redirect(route('welcome', [], false));

            return $response;
        }

    }

    /**
     * Responds with instructions to mothershop
     * @return Services_Twilio_Twiml
     */
    private function _getReturnInstructions()
    {
        $response = new Services_Twilio_Twiml;
        $response->say(
            'To get to your extraction point, get on your bike and go down the' +
            ' street. Then Left down an alley. Avoid the police cars. Turn left' +
            ' into an unfinished housing development. Fly over the roadblock. Go' +
            ' passed the moon. Soon after you will see your mother ship.'
        );
        $response->say($this->_thankYouMessage);
        $response->hangup();

        error_log(var_dump($response));

        return $response;
    }

    /**
     * Responds with instructions to choose a planet
     * @return Services_Twilio_Twiml
     */
    private function _getPlanetInstructions()
    {
        $response = new Services_Twilio_Twiml;
        $response->gather(
            ['numDigits' => '1', 'action' => route('planet_list', [], false)]
        );
        $response->say(
            'To call the planet Brodo Asogi, press 2. To call the planet' +
            ' Dugobah, press 3. To call an Oober asteroid to your location,' +
            ' press 4. To go back to the main menu, press the star key'
        );

    }
}
