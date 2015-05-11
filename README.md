## Documentation

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

### Parameters

``` yaml
# app/config/parameters.yml

upload_template: bootstrap
tmp_upload_dir : uploads
final_upload_dir : media
```

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
    globals:
            upload_template: "%upload_template%"
            tmp_upload_dir: "%tmp_upload_dir%"
            final_upload_dir: "%final_upload_dir%"
        form:
            resources:
            - 'JuiceUploadBundle::%upload_template%_form_fields.html.twig'
```

### Main twig layout

Add CSS:

``` html
{{ include('JuiceUploadBundle:Css:' ~ upload_template ~ '.css.html.twig') }}
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

{{ include('JuiceUploadBundle:Js:' ~ upload_template ~ '.js.html.twig') }}

{% javascripts '@NHBBackendBundle/Resources/public/js/admin.js' %}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```

### Gallery

##### Gallery parent:

``` php

    /**
     * @ORM\ManyToMany(targetEntity="Photos", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinTable(name="upload_images",
     *      joinColumns={@ORM\JoinColumn(name="upload_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true, onDelete="cascade")}
     *      )
     * @ORM\OrderBy({"position" = "ASC"})
     **/
    private $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }
    
```

##### Gallery parent form field

``` php
    ->add('images', 'juice_gallery_field', array(
        'type' => new ImagesType(),
        'label' => 'Gallery',
        'by_reference' => false,
        'multi' => true,
        'options' => array(
            'label' => false
        ),
        'attr' => array(
            'class' => 'offers sortable'
        ),
        'allow_add' => true,
        'allow_delete' => true,
    ))
```

##### Gallery child:

``` php

    /**
     * @ORM\OneToOne(targetEntity="\Juice\UploadBundle\Entity\Media", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $photo;
    
    public function setPhoto($image)
    {
        //clear image object if new image is null on empty
        if ($image == NULL || $image->getFile() == NULL) {
            $this->photo = NULL;

            dump($this->photo);
            return $this;
        }

        if ($this->photo == NULL) {
            $this->photo = $image;
        } else {
            $this->photo->setFile($image->getFile());
        }

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }
```

##### Gallery child form field

``` php

    ->add('photo', 'juice_gallery_child_field', array(
        'required' => false,
        'by_reference' => false,
        'label' => false,
        'field_attr' => array(
            'filter' => 'home_big',
        )
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
    ->add('photo', 'juice_single_image_field', array(
        'required' => false,
        'error_bubbling' => false,
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
        'error_bubbling' => false,
        'by_reference' => false,
        'label' => 'main label name',
        'button_label' => 'button name', // DEFAULT 'Upload' | upload button label
        'accept' => '.png, .jpg', // DEFAULT '.jpg, .png' | upload button label
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
        'label' => 'Upload file', // main label
        'button_label' => 'file upload', // DEFAULT default 'Upload' | upload button label
        'field_attr' => array(
            'data-callback' => 'handleSingleFile' // DEFAULT 'handleSingleFile' | which js function will be triggered after upload
        )
    ))
```