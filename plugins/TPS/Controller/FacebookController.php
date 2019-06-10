<?php

namespace Plugin\SNS_Login\Controller;

use Eccube\Controller\AbstractController;
use Plugin\SNS_Login\Form\Type\Admin\ConfigType;
use Plugin\SNS_Login\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Repository\CustomerRepository;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Eccube\Form\Type\Front\CustomerLoginType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Eccube\Entity\Master\CustomerStatus;

class FacebookController extends AbstractController
{
    private $configRepo;
    private $fb;
    private $encoderFactory;
    private $customerRepository;
    public function __construct(ConfigRepository $configRepo,
        EncoderFactoryInterface $encoderFactory,
        CustomerRepository $customerRepository
    ) {
        $this->configRepo = $configRepo;
        $this->encoderFactory = $encoderFactory;
        $this->customerRepository = $customerRepository;
        $config = $this->configRepo->get();
        $this->fb = new \Facebook\Facebook([
            'app_id' => $config->getFbAppId(), // Replace {app-id} with your app id
            'app_secret' =>  $config->getFbAppsecret(),
            'default_graph_version' => 'v3.2',
        ]);
    }
    /**
     * @Route("/sns_login/facebook", name="sns_login_front_facebook")
     * /@Template("@SNS_Login/admin/config.twig")
     */
    public function connect(Request $request)
    {
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = ['email', 'public_profile']; // Optional permissions
        $loginUrl = $helper->getLoginUrl($request->getSchemeAndHttpHost() . '/sns_login/facebook/callback', $permissions);
        return $this->redirect($loginUrl);
    }


    /**
     * @Route("/sns_login/login", name="sns_login_front_login")
     */
    public function login(Request $request)
    {
        $Customer = $this->customerRepository->find(8);
         // Here, "public" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($Customer, 'fbfbffb', "customer", $Customer->getRoles());

        // For older versions of Symfony, use security.context here
        $this->get("security.token_storage")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        return $this->redirect('/');
    }
    /**
     * @Route("/sns_login/facebook/callback", name="sns_login_front_facebook_callback")
     * /@Template("@SNS_Login/admin/config.twig")
     */
    public function callback(Request $request)
    {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/');
        }

        $helper = $this->fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        try {
        // Get the \Facebook\GraphNodes\GraphUser object for the current user.
        // If you provided a 'default_access_token', the '{access-token}' is optional.
            $response = $this->fb->get('/me?fields=id,first_name,last_name,email', $accessToken);
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $me = $response->getGraphUser();
        $cutomerEmail = $me->getEmail();
        $Customer = $this->customerRepository->findOneBy(['email' => $cutomerEmail]);
        if (!$Customer) {
            // New customer
            $Customer = $this->customerRepository->newCustomer();
            $encoder = $this->encoderFactory->getEncoder($Customer);
            $salt = $encoder->createSalt();
            $password = $encoder->encodePassword('fbfbffb', $salt);
            $secretKey = $this->customerRepository->getUniqueSecretKey();
            $CustomerStatus = $this->entityManager->find(CustomerStatus::class, CustomerStatus::ACTIVE);
            $Customer
                ->setEmail($cutomerEmail)
                ->setName01($me->getFirstName())
                ->setName02($me->getLastName())
                ->setSalt($salt)
                ->setPassword($password)
                ->setSecretKey($secretKey)
                ->setStatus($CustomerStatus)
                ->setPoint(0);
            $this->entityManager->persist($Customer);
            $this->entityManager->flush();
        }

         // Here, "public" is the name of the firewall in your security.yml
        $token = new UsernamePasswordToken($Customer, 'fbfbffb', "customer", $Customer->getRoles());

        // For older versions of Symfony, use security.context here
        $this->get("security.token_storage")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        return $this->redirect('/');
    }
}