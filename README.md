## Documentation

### Composer

``` html
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/dakkor71/UploadBundle.git"
    }
],
```

``` php
php composer.phar require "liip/imagine-bundle" "dev-master"
```

### Routing

``` yaml
juice_upload:
    resource: "@JuiceUploadBundle/Controller/DefaultController.php"
    type:     annotation
```

### Parameters

``` yaml
# app/config/parameters.yml

tmp_upload_dir : uploads
final_upload_dir : media
```

### Config

Add to appKernel.php

``` php
new Juice\UploadBundle\JuiceUploadBundle()
```

``` yaml
# app/config/config.yml

assetic:
    bundles: [ 'JuiceUploadBundle' ]

twig:
    # ...
    globals:
        tmp_upload_dir: "%tmp_upload_dir%"
        final_upload_dir: "%final_upload_dir%"
    form:
        resources:
            - 'JuiceUploadBundle::form_fields.html.twig'
```

### Main twig layout

Add CSS:

``` html
{{ include('JuiceUploadBundle:Default:css.html.twig') }}
```

Add JS. Include JS after (backbone, underscore) and before you init upload object):

**TODO:**

> change this to something simpler


``` html
{% javascripts
    '@NHBBackendBundle/Resources/public/js/bootstrap.js'
    '@JuicejQueryBundle/Resources/public/js/libs/underscore.js'
    '@JuicejQueryBundle/Resources/public/js/libs/backbone.js'
    '@JuicejQueryBundle/Resources/public/js/script.js'
    '@JuicejQueryBundle/Resources/public/js/libs/jquery-ui-1.10.3.custom.js'
%}
<script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}

{{ include('JuiceUploadBundle:Default:js.html.twig') }}

{% javascripts '@NHBBackendBundle/Resources/public/js/admin.js' %}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```

### Gallery

##### Create gallery item entity (point $collection targetEntity to your gallery entity):

``` php
<?php

namespace Demo\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Juice\UploadBundle\Model\CollectionItem as BaseCollectionItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="gallery_item")
 * @ORM\HasLifecycleCallbacks
 */
class GalleryItem extends BaseCollectionItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="items")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $collection;
}

?>
```

##### Create Gallery Entity:

1) Add to entity header:

``` php
use Juice\UploadBundle\Model\Collection as BaseCollection;
```

2) Extend gallery class:

``` php
class Gallery extends BaseCollection
```

3) Connect gallery with you GalleryItem entity

``` php
/**
 * @ORM\OneToMany(targetEntity="GalleryItem", mappedBy="collection", cascade={"persist", "remove"})
 * @ORM\OrderBy({"position" = "ASC", "id" = "DESC"})
 */
protected $items;
```

3) add constructor and setter

``` php
public function __construct()
{
    $this->items = new ArrayCollection();
}

public function addItem(GalleryItem $item)
{
    $this->items->add($item);
    $item->setCollection($this);

    return $this;
}
```

##### Add to form:

min options

``` php
->add('photo', 'juice_single_image_field', array(
    'required' => false,
    'by_reference' => false,
    'field_attr' => array(
        'filter' => 'home_big',
    )
))
```        

full options

``` php
->add('photo', 'juice_single_image_field', array(
    'required' => false,
    'by_reference' => false,
    'label' => main label name',
    'button_label' => 'button name', // DEFAULT default 'Upload' | upload button label
    'field_attr' => array(
        'filter' => 'home_big', // REQUIRED | liip imagine filter which we want to use
        'data-form-kind' => 'image', // DEFAULT 'image' | if type is defined as image, controller will return dimensions arter upload
        'data-callback' => 'handleSingleImage', // DEFAULT 'handleSingleImage' | which js function will be triggered after upload
        'data-crop' => 'true' // DEFAULT default 'false' | if image should be croped (if image is same size as liip filter, it wont be croped)
    )
))
```

### Single photo

##### Add to entity (photo is default name):

``` php
/**
 * @ORM\OneToOne(targetEntity="\Juice\UploadBundle\Entity\Media", cascade={"persist", "remove"}, orphanRemoval=true)
 * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
 */
private $photo;

public function setPhoto($photo)
{
    if ($photo == NULL || $photo->getFile() == NULL) {
        $this->photo = NULL;
    } else {
        $this->photo = $photo;
    }
    return $this;
}

public function getPhoto()
{
    return $this->photo;
}
```

##### Add to form:

min options

``` php
->add('photo', 'juice_single_image_field', array(
    'required' => false,
    'by_reference' => false,
    'field_attr' => array(
        'filter' => 'home_big',
    )
))
```

full options

``` php
->add('photo', 'juice_single_image_field', array(
    'required' => false,
    'by_reference' => false,
    'label' => main label name',
    'button_label' => 'button name', // DEFAULT default 'Upload' | upload button label
    'field_attr' => array(
        'filter' => 'home_big', // REQUIRED | liip imagine filter which we want to use
        'data-form-kind' => 'image', // DEFAULT 'image' | if type is defined as image, controller will return dimensions arter upload
        'data-callback' => 'handleSingleImage', // DEFAULT 'handleSingleImage' | which js function will be triggered after upload
        'data-crop' => 'true' // DEFAULT default 'false' | if image should be croped (if image is same size as liip filter, it wont be croped)
    )
))
```

### Single file

##### Add to entity (file is default name):

``` php
/**
 *
 * @ORM\OneToOne(targetEntity="\Juice\UploadBundle\Entity\Media", cascade={"persist", "remove"}, orphanRemoval=true)
 * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
 */
private $file;

public function setFile($file)
{
    if ($file == NULL || $file->getFile() == NULL) {
        $this->file = NULL;
    } else {
        $this->file = $file;
    }

    return $this;
}

public function getFile()
{
    return $this->file;
}
```

##### Add to form:

min options

``` php
->add('file', 'juice_single_file_field', array(
    'required' => false,
    'by_reference' => false
))
```

full options

``` php
->add('file', 'juice_single_file_field', array(
    'required' => false,
    'by_reference' => false,
    'label' => 'bla bla bla', // main label
    'button_label' => 'file upload', // DEFAULT default 'Upload' | upload button label
    'field_attr' => array(
        'data-callback' => 'handleSingleFile' // DEFAULT 'handleSingleFile' | which js function will be triggered after upload
    )
))
```


