<?php

namespace common\models;

use Imagine\Image\Box;
use yii\db\Expression;
use yii\helpers\Html;
use yii\imagine\Image;
use Yii;

class ProductMedia extends \yii\db\ActiveRecord
{
    public $file = null;
    public $filePreview = null;
    public $removeImage = null;
    public $removeImagePreview = null;
    private $_assetsPath = '@backend/web/uploads/product-media';

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 9;

    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 5;

    private static $_statuses = [
        self::STATUS_ACTIVE => 'Активно',
        self::STATUS_DELETED => 'Удалено',
    ];

    private static $_types = [
        self::TYPE_IMAGE => 'Изображение',
        self::TYPE_VIDEO => 'Видео',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%product_media}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'removeImage', 'removeImagePreview'], 'safe'],
            [['file'], 'file'],
            [['status_id', 'type_id', 'is_primary', 'sort', 'product_id'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function upload()
    {
        if (!$this->file)
            return false;

        $date = date('Y-m-d', time());
        $dirName = Yii::getAlias($this->_assetsPath);
        if (!is_dir($dirName . '/' . $date)) mkdir($dirName . '/' . $date, 0775, true);

        if ($this->file) {
            $fileName = uniqid() . '.' . $this->file->getExtension();
            $this->file->saveAs($dirName . '/' . $date . '/' . $fileName);
            $this->url = $date . '/' . $fileName;
            $this->file = false;
        }

        return $this->url;
    }

    public function getPhoto($width, $height, $method = 'resize', $path = null)
    {
        $image = $this->url;
        if ($path)
            $image = $path;

        if ($image) {
            $pathDir = Yii::getAlias('@next/public/images/cache/product-media');
            $origDir = Yii::getAlias('@backend/web/uploads/product-media');

            $salt = '';
            if (is_file($origDir . '/' . $image))
                $salt = sha1_file($origDir . '/' . $image);

            if (!is_dir($pathDir))
                mkdir($pathDir, 0775, true);

            if ($method == 'resize') {
                $imageCache = $pathDir . '/r-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';

                if (!is_file($imageCache)) {
                    $pathImage = $origDir . '/' . $image;
                    if (is_file($pathImage)) {
                        if ($width == 0 && $height != 0) {
                            $sizes = getimagesize(Yii::getAlias($pathImage));
                            $width = $height * $sizes[0]/$sizes[1];
                        } elseif ($width != 0 && $height == 0) {
                            $sizes = getimagesize(Yii::getAlias($pathImage));
                            $height = $width * $sizes[1]/$sizes[0];
                        }

                        $box = new Box($width, $height);

                        Image::getImagine()->open($pathImage)->resize($box)->save($imageCache, ['quality' => 90]);
                    }
                }

                if (is_file($imageCache))
                    return '/images/cache/product-media/r-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';;

            } elseif ($method == 'thumb') {
                $imageCache = $pathDir . '/t-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';

                if (!is_file($imageCache)) {
                    $newW = $width;
                    $newH = $height;
                    $pathImage = Yii::getAlias('@backend/web/uploads/product-media') . '/' . $image;

                    if (is_file($pathImage)) {
                        $temp = Image::getImagine()->load(file_get_contents($pathImage));
                        $size = $temp->getSize();

                        if (($size->getWidth() / $size->getHeight()) > ($width / $height))
                            $newW = $newH * ($size->getWidth() / $size->getHeight());
                        else
                            $newH = ($size->getHeight() / $size->getWidth()) * $newW;
                        Image::resize($pathImage, $newW, $newH, true, true)
                            ->thumbnail(new Box($width, $height), \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND)
                            ->save($imageCache, ['jpeg_quality' => 90]);
                    }

                }

                if (is_file($imageCache))
                    return '/images/cache/product-media/t-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';;;

            } elseif ($method == 'fit') {
                $imageCache = $pathDir . '/f-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';

                if (!is_file($imageCache)) {
                    $pathImage = Yii::getAlias('@backend/web/uploads/product-media') . '/' . $image;

                    if (is_file($pathImage)) {
                        Image::thumbnail($pathImage, $width, $height, \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET)
                            ->save($imageCache, ['jpeg_quality' => 90]);
                    }
                }

                if (is_file($imageCache))
                    return '/images/cache/product-media/f-' . $salt . '-media-' . $width . 'x' . $height . '-' . $this->id . '.jpg';;;
            }
        }

        return '';
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getPathPicture($path = null)
    {
        $image = $this->url;
        if ($path)
            $image = $path;

        if ($image)
            return '/uploads/product-media/' . $image;
    }

    public function getPicture($path = null)
    {
        $image = $this->url;
        if ($path)
            $image = $path;

        if ($image) {
            $src = $this->getPathPicture($image);
            return '<div style="width: 170px; height: 90px">' . Html::img($src, ['style' => 'max-width:100%; max-height:100%;']) . '</div>';
        }
        return Yii::t('new', 'Not found');
    }

    public function getVideoThumbnail($quality = "default") {
        if($this->type_id == self::TYPE_VIDEO && $this->url) {
            $parsedUrl = explode('=', $this->url);

            $thumbQuality = $quality == "default" ? '/default.jpg' : '/maxresdefault.jpg';

            if(is_array($parsedUrl)) {
                $previewPath = 'http://img.youtube.com/vi/' . array_pop($parsedUrl) . $thumbQuality;
                return $previewPath;
            }
        }
    }

    public static function getStatuses()
    {
        return self::$_statuses;
    }

    public function getStatus($id = 0)
    {
        if (!$id) $id = $this->status_id;
        return ((!empty(self::$_statuses[$id])) ? self::$_statuses[$id] : Yii::t('product', 'No Specify'));
    }

    public static function getTypes()
    {
        return self::$_types;
    }

    public function getType($id = 0)
    {
        if (!$id) $id = $this->type_id;
        return ((!empty(self::$_types[$id])) ? self::$_types[$id] : Yii::t('product', 'No Specify'));
    }
}