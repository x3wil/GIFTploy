<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\Adapter\Ftp;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Event\LifecycleEventArgs;
use GIFTploy\Filesystem\ServerInterface;

/**
 * Repository
 *
 * @ORM\Table(name="server_ftp")
 * @ORM\Entity(repositoryClass="Entity\ServerFtpRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ServerFtp implements ServerInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min = "3", max = "30")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $host;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(name="port", type="integer")
     */
    private $port;

    /**
     * @var integer
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @var boolean
     *
     * @ORM\Column(name="passive", type="boolean")
     */
    private $passive;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ssl_connection", type="boolean")
     */
    private $sslConnection;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer")
     * @Assert\GreaterThan(value = 0)
     */
    private $timeout;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createdAt = new \DateTime();
        $this->sslConnection = false;
    }

    public function __construct()
    {
        if ($this->isNew()) {
            $this->setPort(21);
            $this->setTimeout(10);
        }
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function isNew()
    {
        return !((bool)$this->getId());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ServerFtp
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return ServerFtp
     */
    public function setHost($host)
    {
        $this->host = $host;
    
        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return ServerFtp
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return ServerFtp
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set port
     *
     * @param integer $port
     * @return ServerFtp
     */
    public function setPort($port)
    {
        $this->port = $port;
    
        return $this;
    }

    /**
     * Get port
     *
     * @return integer 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return ServerFtp
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set passive
     *
     * @param boolean $passive
     * @return ServerFtp
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
    
        return $this;
    }

    /**
     * Get passive
     *
     * @return boolean 
     */
    public function getPassive()
    {
        return $this->passive;
    }

    /**
     * Set sslConnection
     *
     * @param boolean $sslConnection
     * @return ServerFtp
     */
    public function setSslConnection($sslConnection)
    {
        $this->sslConnection = $sslConnection;
    
        return $this;
    }

    /**
     * Get sslConnection
     *
     * @return boolean 
     */
    public function getSslConnection()
    {
        return $this->sslConnection;
    }

    /**
     * Set timeout
     *
     * @param integer $timeout
     * @return ServerFtp
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    
        return $this;
    }

    /**
     * Get timeout
     *
     * @return integer 
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return ServerFtp
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return ServerFtp
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return ServerFtp
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Returns configuration array for Flysystem adapter.
     *
     * @return array
     */
    public function getConfiguration()
    {
        return [
            'host' => $this->getHost(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'port' => $this->getPort(),
            'root' => $this->getPath(),
            'passive' => $this->getPassive(),
            'ssl' => $this->getSslConnection(),
            'timeout' => $this->getTimeout(),
        ];
    }

    /**
     * @return \League\Flysystem\Adapter\Ftp
     */
    public function getAdapter()
    {
        return new Ftp($this->getConfiguration());
    }
}
