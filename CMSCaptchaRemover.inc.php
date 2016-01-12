<?php

/**
 * Class CMSCaptchaRemover
 * @author Vitalii Puhach
 * @copyright ProjektCS 2016
 */
class CMSCaptchaRemover
{
    private $providerId;
    private $brandId;

    const IMG_PATH = "files/upload/";

    /**
     * CMSCaptchaRemover constructor.
     * @param $providerId
     * @param $brandId
     */
    public function __construct($providerId, $brandId)
    {
        if (!$providerId || !$brandId) die('CMSCaptchaRemover __construct error');

        $this->providerId = $providerId;
        $this->brandId = $brandId;
    }

    /**
     * @return mixed
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @return mixed
     */
    public function getBrandId()
    {
        return $this->brandId;
    }

    /**
     * @param int $providerId
     * @throws CMSException
     */
    public static function showProviderBrands($providerId)
    {
        $query = "SELECT `brand`.`brand_id`, `brand`.`brand_title`, `brand`.`brand_valid`
                    FROM `amz_glasses_brand` AS `brand`
                    LEFT JOIN `amz_glasses_provider` AS `provider` ON `provider`.`provider_id` = `brand`.`provider_id`
                    WHERE `provider`.`provider_id` = :provider_id";
        $q = CMSPluginDb::getInstance()->getQuery($query);
        $q->setInt('provider_id', $providerId);
        $data = $q->execute();

        $brandData = $data->getData();

        print_r($brandData);
    }


    public function getImagesDataByBrand()
    {
        $query = "SELECT
                      `file`.`file_name`,
                      `file`.`file_id`,
                      `detail`.`detail_id`,
                      CONCAT(:img_path, LPAD((FLOOR((`file`.`file_id` / 1000)) * 1000), 6, 0), '/', LPAD(`file`.`file_id`, 6, 0), '/', `file`.`file_name`) AS `img_path`
                    FROM `amz_file` AS `file`
                    INNER JOIN `amz_glasses_item_detail` AS `detail` ON `detail`.`file_id` = `file`.`file_id`
                    LEFT JOIN `amz_glasses_item` AS `item` ON `item`.`item_id` = `detail`.`item_id`
                    LEFT JOIN `amz_glasses_brand` AS `brand` ON `brand`.`brand_id` = `item`.`brand_id`
                    WHERE `brand`.`brand_id` = :brand_id
                    AND `item`.`is_valid` = :is_valid";
        $q = CMSPluginDb::getInstance()->getQuery($query);
        $q->setInt('brand_id', $this->brandId);
        $q->setInt('is_valid', 1);
        $q->setText('img_path', DOCROOT . self::IMG_PATH);
        $data = $q->execute();

        $imgData = $data->getData();

        if (empty($imgData)) {
            die("No one image for brand!");
        }

        return $imgData;
    }

    public function deleteImgCaptchaAndSave($imagesData) {
        foreach($imagesData as $oneImageData) {
            $image = $oneImageData['img_path'];

            if(!file_exists($image))  {
                echo "Wrong image - ". $image ."\n";
                continue;
            }

            list($width, $height) = getimagesize($image);
            $filesize = round((filesize($image) / 1024), 2).' KB';

            $this->drawRectangleAndReplaceExistImg($image, $height, $width);

            echo '<a href="http://108.59.12.8/files/upload/'.sprintf('%06d', (intval($oneImageData['file_id'] / 1000) * 1000)).'/'.sprintf('%06d', $oneImageData['file_id']).'/'.$oneImageData['file_name'].'" target="blank">Source: '.$width.'x'.$height.', '.$filesize.' was done! [detail id -'. $oneImageData['detail_id'] .']</a> <br>';
        }
    }

    private function drawRectangleAndReplaceExistImg($image, $height, $width) {
        // прямоугольник рисуем снизу изображения высотой 8% от высоты изображения
        $heightRectagle = $height * 0.08;

        $gmagicDraw = new GmagickDraw();
        $gmagicDraw->setfillcolor("#fff");
        $gmagicDraw->rectangle(0, $height - $heightRectagle, $width, $height);

        $gImage = new Gmagick();
        $gImage->readImage($image);
        $gImage->drawimage($gmagicDraw);
        $gImage->writeimage($image);
        $gImage->destroy();
    }

}