<?php

namespace Plugin\SNS_Login\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_sns_login_config")
 * @ORM\Entity(repositoryClass="Plugin\SNS_Login\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_app_id", type="string", length=255)
     */
    private $fb_app_id;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_app_secret", type="string", length=255)
     */
    private $fb_app_secret;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of fb_app_id
     *
     * @return  string
     */
    public function getFbAppId()
    {
        return $this->fb_app_id;
    }

    /**
     * Set the value of fb_app_id
     *
     * @param  string  $fb_app_id
     *
     * @return  self
     */
    public function setFbAppId(string $fb_app_id)
    {
        $this->fb_app_id = $fb_app_id;

        return $this;
    }

    /**
     * Get the value of fb_app_secret
     *
     * @return  string
     */
    public function getFbAppSecret()
    {
        return $this->fb_app_secret;
    }

    /**
     * Set the value of fb_app_secret
     *
     * @param  string  $fb_app_secret
     *
     * @return  self
     */
    public function setFbAppSecret(string $fb_app_secret)
    {
        $this->fb_app_secret = $fb_app_secret;

        return $this;
    }
}
