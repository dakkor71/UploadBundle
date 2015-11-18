## Documentation

**TODO:**

> documentation outdated

### Composer

``` html
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/dakkor71/UploadBundle.git"
    }
],
"require": [
    "juice/uploadbundle": "dev-master"
]
```

### Routing

``` yaml
juice_upload:
    resource: "@JuiceUploadBundle/Controller/"
    type:     annotation
```

TIP: you can add some prefix which will be protected by symfony2 security component

### Config


Add to appKernel.php

``` php

new Liip\ImagineBundle\LiipImagineBundle(),
new Juice\UploadBundle\JuiceUploadBundle(),
```

``` yaml

# app/config/config.yml

assetic:
    bundles: [ 'JuiceUploadBundle' ]

twig:
    # ...
    form:
        resources:
        - 'JuiceUploadBundle::default_form_fields.html.twig'
        
juice_upload: # optional, default values:
    absolute_path: true
    tmp_upload_dir: "uploads"
    final_upload_dir: "media"
    
```

If your symfony2 is in subfolder you have to change absolute_path to false and also add base metatag to main template like this:

``` twig
<base href="{{ app.request.baseUrl }}/">
```

### Main twig layout

Replace "default" to "bootstrap" in css and js files if you are using bootstrap layout.

Add CSS:

``` html
{{ include('JuiceUploadBundle:Css:default.css.html.twig') }}
```

Add JS. Include JS after (backbone, underscore) and before you init upload object):

**TODO:**

> change this to something simpler


``` html
{% javascripts
    '@JuiceUploadBundle/Resources/public/js/libs/jquery-1.11.3.min.js'
    '@JuiceUploadBundle/Resources/public/js/libs/underscore-min.js'
    '@JuiceUploadBundle/Resources/public/js/libs/backbone-min.js'
%}
<script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}

{{ include('JuiceUploadBundle:Js:default.js.html.twig') }}

{% javascripts '@NHBBackendBundle/Resources/public/js/admin.js' %}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```

### Upload init

``` html
var customUploadView = uploadView.extend({});

uploadView = new customUploadView({
    el: $('.juice_upload_container')
});
```

### Gallery container entity

``` php

    /**
     * @ORM\OneToMany(targetEntity="GalleryItem", mappedBy="gallery", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     **/
    private $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getImages() {
        return $this->images;
    }

    public function addImage(GalleryItem $galleryItem)
    {
        $galleryItem->setGallery($this);
        $this->images->add($galleryItem);
    }

    public function removeImage(GalleryItem $galleryItem)
    {
        $this->images->removeElement($galleryItem);
    }
    
```

##### gallery container form field

``` php
    ->add('images', 'juice_upload_gallery_field', array(
        'type' => new GalleryItemType(),
        'label' => 'Images',
        'field_attr' => array(
            'filter' => 'filter',
        )
    ))
```

##### gallery container form field full

``` php
    ->add('images', 'juice_gallery_field', array(
        'type' => new ImagesType(),
        'label' => 'Gallery',
        'options' => array(
            'attr' => array(
                'class' => 'juice_upload_gallery_item',
            )
        ),
        'field_attr' => array(
            'filter' => 'home_big',
            'data-form-kind' => 'image',
            'data-callback' => 'handleGalleryImage',
            'data-crop' => 'false',
        )
    ))
```

##### Gallery item entity:

``` php

    <?php

    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;

    /**
     * Gallery
     *
     * @ORM\Table(name="gallery_item")
     * @ORM\Entity()
     */
    class GalleryItem
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
         * @ORM\OneToOne(targetEntity="\Juice\UploadBundle\Entity\Media", cascade={"persist", "remove"}, orphanRemoval=true)
         * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
         */
        private $image;

        /**
         * @ORM\ManyToOne(targetEntity="Day", inversedBy="images")
         * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
         */
        private $gallery;

        /**
         * @var string
         *
         * @ORM\Column(name="position", type="integer")
         */
        private $position;

        public function getId()
        {
            return $this->id;
        }

        public function setImage($image)
        {
            //clear image object if new image is null on empty
            if ($image == NULL || $image->getFile() == NULL) {
                $this->image = NULL;
                return $this;
            }

            if ($this->image == NULL) {
                $this->image = $image;
            } else {
                $this->image->setFile($image->getFile());
            }

            return $this;
        }

        public function getImage()
        {
            return $this->image;
        }

        public function setPosition($position)
        {
            $this->position = $position;

            return $this;
        }

        public function getPosition()
        {
            return $this->position;
        }

        public function setGallery($gallery)
        {
            return $this->gallery = $gallery;
        }
    }
```

### Gallery item form

``` php
     $builder
         ->add('image', 'juice_upload_gallery_child_field', array(
             'label' => false
         ))
         ->add('position', 'hidden', array(
             'attr' => array('class' => 'position')
         ))
     ;
```

### Gallery Sorting

##### Gallery container entity

To add sorting you have to add 

``` php
     * @ORM\OrderBy({"position" = "ASC"})
```

to gallery collection entity and position fields to gallery Item entity.

##### Gallery Item entity

position form fields should contain proper class

``` php
     ->add('position', 'hidden', array(
         'attr' => array('class' => 'position')
     ))
```

### Single image

##### Add to entity:

``` php
    /**
     * @ORM\OneToOne(targetEntity="\Juice\UploadBundle\Entity\Media", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $image;
    
    public function setImage($image)
    {
        //clear image object if new image is null on empty
        if ($image == NULL || $image->getFile() == NULL) {
            $this->image = NULL;
            return $this;
        }
    
        if ($this->image == NULL) {
            $this->image = $image;
        } else {
            $this->image->setFile($image->getFile());
        }
    
        return $this;
    }
    
    public function getImage()
    {
        return $this->image;
    }
```

##### Add to form:

min options

``` php
    ->add('photo', 'juice_upload_image_field', array(
        'field_attr' => array(
            'filter' => 'home_big',
        )
    ))
```

If you want to add some custom class to upload container check full options

full options

``` php
    ->add('photo', 'juice_upload_image_field', array(
        'label' => 'main label name',
        'button_label' => 'button name', // DEFAULT 'Upload' | upload button label
        'accept' => '.png, .jpg', // DEFAULT '.jpg, .png' | upload button label
        'attr' => array(
            'class' => 'juice_upload someCustomClass'
        ),
        'field_attr' => array(
            'filter' => 'home_big', // REQUIRED | liip imagine filter which we want to use
            'data-form-kind' => 'image', // DEFAULT 'image' | if type is defined as image, controller will return dimensions arter upload
            'data-callback' => 'handleSingleImage', // DEFAULT 'handleSingleImage' | which js function will be triggered after upload
            'data-crop' => 'true', // DEFAULT default 'false' | if image should be croped (if image is same size as liip filter, it wont be croped)
        )
    ))
```



### Single file

##### Add to entity:

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
            return $this;
        }
    
        if ($this->file == NULL) {
            $this->file = $file;
        } else {
            $this->file->setFile($file->getFile());
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
    ->add('file', 'juice_upload_file_field')
```

full options

``` php
    ->add('file', 'juice_upload_file_field', array(
        'label' => 'Upload file', // main label
        'button_label' => 'file upload', // DEFAULT default 'Upload' | upload button label
        'accept' => '.txt',
        'field_attr' => array(
            'data-callback' => 'handleSingleFile' // DEFAULT 'handleSingleFile' | which js function will be triggered after upload
        ),
        'attr' => array(
            'class' => 'juice_upload someCustomClass'
        )
    ))
```