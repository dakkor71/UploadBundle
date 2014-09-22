yo yo yo !!

TODO calosc jest pisana przy pomocy backbone oraz bootstrap i trzeba zapewnic aby byly zamieszczone w projekcie - tutaj moze jakis require.js by sie przydal lub cus innego

TODO projekt wykouzystuje liip imagine i bez niego ani rusz, mozna by pomyslec czy nie da sie zrobic jakiegos defaultowego handlera co cropuje po stronie php i nie wymaga instalacji liip imagine

========================
parameters.yml
========================
dodac foldery tmp i final

    tmp_upload_dir : uploads
    final_upload_dir : media



========================
config.yml
========================
trzeba dodac tutaj taki wpis aby mogl compilowac CSS i JS z includowanych plików

assetic:
    bundles: [ 'JuiceUploadBundle' ]

trzeba dodać do twiga globalne zmienne

twig:
    # ...
    globals:
        tmp_upload_dir: "%tmp_upload_dir%"
        final_upload_dir: "%final_upload_dir%"



========================
cms.html.twig
========================
( lub inny glowny template CMS'a ) dodajemy CSS i JS odpowiedzialne za upload + crop
TODO Wymaga to niestety aby scrypt odpalajacy wrzucic pod koniec i jest niewygodne (przyklad ponizej)
W js.html.twig tworzony jest tez config i mozna z tych wartosci kozystac w swoim skrypcie


{{ include('JuiceUploadBundle:Default:css.html.twig') }}
{{ include('JuiceUploadBundle:Default:js.html.twig') }}

=========
PRZYKŁAD:
=========

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

========================
UploadBundle - form_fields.html.twig
========================
jest tam rozszerzenie formsow ktore zamienia inputy w przyciski do uploadu.
TODO Narazie tylko singleImage jest powiedzmy ze gotowe galleryCollection można by dopracować pod katem rozszerzalnosci.
TODO Mam tutaj na mysli sama galerie ale rownierz wrzucanie kolekcji plików oraz roznego rodzaju dodatkowe pola textowe. ponizej kod pozwalajacy dodac kilka templatek do forma

{% form_theme form with ['NHBBackendBundle::form_fields.html.twig', 'JuiceUploadBundle::form_fields.html.twig'] %}

========================
Entity
========================
TODO : GalleryCollection - ale mam to zrobione w innym projekcie pozniej przeklepie
SingleForm w UploadBundle - rozszerzamy nasza entitke o ta klase i dodajemy kilka linijek

* @ORM\HasLifecycleCallbacks

/**
 * @ORM\PostPersist()
 * @ORM\PostUpdate()
 */
public function preFlush() {
    $this->upload($this->photo , 'File'); - tutaj wpisujemy nazwy warotsci objektu do uploadu oraz jego nazwa potrzebna do update'u
}

/**
 * @ORM\PostRemove()
 */
public function postRemove() {
    $this->removeFile($this->photo , 'File'); - tutaj wpisujemy nazwy warotsci objektu do uploadu oraz jego nazwa potrzebna do update'u
}

========================
FormType
========================
'image_type' dostepny przez service
TODO: trzeba zrobic zabezpieczenia zeby nie strzelal errorami jak czegos nie dodasz; nie wszystkie oopcje sa wymagane

 ->add('photo', 'image_type', array(
    'attr' => array(
        'filter' => 'header_vertical_bg',
        'data-form-kind' => 'image',
        'data-callback' => 'handleSingleImage',
        'data-crop' => 'true',
    )
))




