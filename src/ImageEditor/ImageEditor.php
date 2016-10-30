<?php
namespace RudyMas\ImageEditor;

/**
 * Class ImageEditor (Quick edits for images)
 *
 * @author      Rudy Mas <rudy.mas@rudymas.be>
 * @copyright   2014 - 2016, rudymas.be. (http://www.rudymas.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     0.3.0
 */
class ImageEditor
{
    private $imageName;
    private $imageOriginalLength;
    private $imageOriginalHeight;
    private $imageOriginal;
    private $imageNewLength;
    private $imageNewHeight;
    private $imageNew;

    /**
     * ImageEditor constructor.
     *
     * @param string    $file   The image file to process
     */
    public function __construct($file)
    {
        if (!is_file($file)) die("File '$file' doesn't exist on the server.");
        $this->imageInfo = getimagesize($file);
        switch ($this->imageInfo['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file);
                break;
            case 'image/gif':
                $tempImage = imagecreatefromgif($file);
                $image = imagecreatetruecolor($this->imageInfo[0], $this->imageInfo[1]);
                imagecopy($image, $tempImage, 0, 0, 0, 0, $this->imageInfo[0], $this->imageInfo[1]);
                break;
            default:
                die("No support for this image type!");
        }
        $this->imageName = $file;
        $this->imageOriginalLength = imagesx($image);
        $this->imageOriginalHeight = imagesy($image);
        $this->imageOriginal = $image;
    }

    /**
     * Freeing up memory
     */
    public function __destruct()
    {
        imagedestroy($this->imageOriginal);
        imagedestroy($this->imageNew);
    }

    /**
     * Creating a new resized image
     *
     * @param int   $length The new maximum length of the image
     * @param int   $height The new maximum height of the image
     * @param bool  $resize Set to FALSE to do a resampling, TRUE for resizing (Default: FALSE)
     */
    public function imageResize($length, $height, $resize = FALSE)
    {
        if ($length == 0 && $height != 0) {
            $lengthNew = floor($this->imageOriginalLength * ($height / $this->imageOriginalHeight));
            $heightNew = $height;
        } elseif ($length != 0 && $height == 0) {
            $lengthNew = $length;
            $heightNew = floor($this->imageOriginalHeight * ($length / $this->imageOriginalLength));
        } else {
            $lengthNew = $length;
            $heightNew = floor($this->imageOriginalHeight * ($length / $this->imageOriginalLength));
            if ($heightNew > $height) {
                $lengthNew = floor($this->imageOriginalLength * ($height / $this->imageOriginalHeight));
                $heightNew = $height;
            }
        }

        $newImage = imagecreatetruecolor($lengthNew, $heightNew);
        imagealphablending($newImage, FALSE);
        if ($resize) imagecopyresized($newImage, $this->imageOriginal, 0, 0, 0, 0, $lengthNew, $heightNew, $this->imageOriginalLength, $this->imageOriginalHeight);
        else imagecopyresampled($newImage, $this->imageOriginal, 0, 0, 0, 0, $lengthNew, $heightNew, $this->imageOriginalLength, $this->imageOriginalHeight);

        $this->imageNew = $newImage;
        $this->imageNewLength = imagesx($newImage);
        $this->imageNewHeight = imagesy($newImage);
    }

    /**
     * Saving the new image to the server
     *
     * @param string    $newFileName    The filename for the now image
     */
    public function imageSave($newFileName)
    {
        $extension = strtolower(pathinfo($newFileName, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($this->imageNew, $newFileName);
                break;
            case 'png':
                imagepng($this->imageNew, $newFileName);
                break;
            case 'gif':
                imagegif($this->imageNew, $newFileName);
                break;
            default:
                die("File extension '{$extension}' not supported yet.");
                break;
        }
    }
}
/** End of File: ImageEditor.php **/