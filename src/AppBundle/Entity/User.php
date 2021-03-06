<?php
// src/AppBundle/Entity/User.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\AlbumItem;
use AppBundle\Entity\Playlist;
use Doctrine\ORM\EntityManager;

/**
 * @ORM\Table(name="User")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     *
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive;

    // één user heeft één playlist met uploads, één playlist met uploads heeft één user
    /**
     * @ORM\OneToOne(targetEntity="Playlist")
     */
    private $uploads;

    // één user heeft meerdere playlists, één playlist heeft één user
    /**
     * @ORM\OneToMany(targetEntity="Playlist", mappedBy="user")
     */
    private $playLists;

    // één user heeft meerdere Albums, één Album heeft meerdere Users
    /**
     *
     * @ORM\ManyToMany(targetEntity="Album")
     * @ORM\JoinTable(name="user_album")
     */
    private $albums;

    // één user heeft meerdere Artists
    /**
     *
     * @ORM\ManyToMany(targetEntity="Artist")
     * @ORM\JoinTable(name="user_artist")
     */
    private $artists;

    public function __construct()
    {
        $this->isActive = true;
        $this->playLists = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->artists = new ArrayCollection();
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return $this->getIsActive();
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->getIsActive();
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return $this->getIsActive();
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->getIsActive();
    }



    /**
     * Add playLists
     *
     * @param \AppBundle\Entity\Playlist $playList
     * @return User
     */
    public function addPlayList(\AppBundle\Entity\Playlist $playList)
    {
        // in reality we add the user to the playlist, and not the other way around
        $playList->setUser($this);
        // adding the playlist to the arrayCollection
        $this->playLists->add($playList);

        return $this;
    }

    /**
     * Remove playLists
     *
     * @param \AppBundle\Entity\Playlist $playLists
     */
    public function removePlayList(\AppBundle\Entity\Playlist $playLists)
    {
        $this->playLists->removeElement($playLists);
    }

    /**
     * Get playLists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlayLists()
    {
        return $this->playLists;
    }

    /**
     * Get playLists
     *
     * @param string $listName
     * @return \AppBundle\Entity\Playlist
     *
     */
    public function getPlayListByName($listName){

        for($i = 0; $i < count($this->playLists); $i++)
        {
            if( strcmp($this->playLists->get($i)->getListName(), $listName) == 0 ) {
                return $this->playLists->get($i);
            }
        }
    }

    /**
     * Get playLists
     *
     * @param string $listName
     * @return \AppBundle\Entity\Playlist
     *
     */
    public function getPlayListById($listId){

        for($i = 0; $i < count($this->playLists); $i++)
        {
            if( $this->playLists->get($i)->getId() == $listId) {
                return $this->playLists->get($i);
            }
        }
    }

    /**
     * Get the name and id of the playlists
     *
     * @return array
     */
    public function getPlaylistData()
    {
        $data = array();
        for($i = 0; $i < count($this->playLists); $i++)
        {
            $playlist = $this->playLists->get($i);
            $data[$i] = (object) array('id' => $playlist->getId(), 'listName' => $playlist->getListName());
        }
        return $data;
    }

    /**
     * Get data for pickers
     *
     * @return array
     */
    public function getPlaylistPickerData()
    {
        $data = array();
        for($i = 0; $i < count($this->playLists); $i++)
        {
            $playlist = $this->playLists->get($i);
            $data[$playlist->getId()] = $playlist->getListName();
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * Get data for pickers
     *
     * @return array
     */
    public function getAlbumsPickerData()
    {
        $data = array();
        for($i = 0; $i < count($this->albums); $i++)
        {
            $album = $this->albums->get($i);
            $data[$album->getId()] = $album->getName();
        }
        return $data;
    }

    /**
     * Get the name and id of the playlists
     *
     * @return array
     */
    public function getAlbumData()
    {
        $data = array();
        for($i = 0; $i < count($this->albums); $i++)
        {
            $album = $this->albums->get($i);
            $data[$i] = (object) array('id' => $album->getId(), 'name' => $album->getName());
        }
        return $data;
    }

    /**
     * @param mixed $albums
     */
    public function setAlbums($albums)
    {
        $this->albums = $albums;
    }

    /**
     * @param \AppBundle\Entity\Album $album
     */
    public function addAlbum($album)
    {
        /* add the albumItem to albums*/
        $this->albums->add($album);
    }

    /**
     * Get album
     *
     * @param string $albumName
     * @return \AppBundle\Entity\Album
     *
     */
    public function getAlbumByName($albumName){

        for($i = 0; $i < count($this->albums); $i++)
        {
            if( strcmp($this->albums->get($i)->getName(), $albumName) == 0 ) {
                return $this->albums->get($i);
            }
        }
    }

    /**
     * Get Album
     *
     * @param string $albumId
     * @return \AppBundle\Entity\Album
     *
     */
    public function getAlbumById($albumId){

        for($i = 0; $i < count($this->albums); $i++)
        {
            if( $this->albums->get($i)->getId() == $albumId) {
                return $this->albums->get($i);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    /**
     * @param \AppBundle\Entity\Playlist $uploads
     */
    public function setUploads($uploads)
    {
        $this->uploads = $uploads;
    }

    /**
     * @return mixed
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @param mixed $artists
     */
    public function setArtists($artists)
    {
        $this->artists = $artists;
    }

    /**
     * @param \AppBundle\Entity\Artist $artist
     */
    public function addArtist($artist)
    {
        $this->artists->add($artist);
    }

    /**
     * Get data for pickers
     *
     * @return array
     */
    public function getArtistsPickerData()
    {
        $data = array();
        for($i = 0; $i < count($this->artists); $i++)
        {
            $artist = $this->artists->get($i);
            $data[$artist->getId()] = $artist->getName();
        }
        return $data;
    }


    /**
     * Get data for pickers
     * @param int $artistId
     * @return array
     */
    public function getArtistById($artistId)
    {
        for($i = 0; $i < count($this->artists); $i++)
        {
            if( $this->artists->get($i)->getId() == $artistId) {
                return $this->artists->get($i);
            }
        }

    }
}
