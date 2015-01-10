<?php

namespace multikanban\multikanban\Controller;

use multikanban\multikanban\Controller\BaseController;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use multikanban\multikanban\Security\Token\ApiToken;

class TokenController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        //$controllers->post('/tokens', array($this, 'createAction'));
        $controllers->get('/tokens', array($this, 'getAction'));

    }

    /*
     * Request to create a token for a given basic auth.
     */
    public function createAction(Request $request)
    {
        
        $this->enforceUserSecurity();

        $data = $this->decodeRequestBodyIntoParameters($request);

        $token = new ApiToken($this->getLoggedInUser()->id);
        $token->notes = $data->get('notes');

        $errors = $this->validate($token);
        if ($errors) {
            $this->throwApiProblemValidationException($errors);
        }

        $this->getApiTokenRepository()->save($token);

        return $this->createApiResponse($token, 201);
    }

    /*
     * Get the token of a user by a given basic auth
     */
    public function getAction(){

        $this->enforceUserSecurity();

        $token = $this->getApiTokenRepository()->findOneById($this->getLoggedInUser()->id);

        return $this->createApiResponse($token, 200);
    }
}
