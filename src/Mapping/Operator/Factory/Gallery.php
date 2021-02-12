<?php


namespace Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\Factory;



use Pimcore\Bundle\DataHubBatchImportBundle\Exception\InvalidConfigurationException;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\AbstractOperator;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Type\TransformationDataTypeService;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\ImageGallery;

class Gallery extends AbstractOperator
{

    public function process($inputData, bool $dryRun = false)
    {
        $items = [];

        if(!is_array($inputData)) {
            $inputData = [$inputData];
        }

        foreach($inputData as $asset) {
            if($asset instanceof Asset) {
                $hotspotImage = new Hotspotimage($asset);
                $items[] = $hotspotImage;
            }
        }

        return new ImageGallery($items);
    }

    /**
     * @param string $inputType
     * @param int|null $index
     * @return string
     * @throws InvalidConfigurationException
     */
    public function evaluateReturnType(string $inputType, int $index = null): string {

        if(!in_array($inputType, [TransformationDataTypeService::ASSET, TransformationDataTypeService::ASSET_ARRAY])) {
            throw new InvalidConfigurationException(sprintf("Unsupported input type '%s' for gallery operator at transformation position %s", $inputType, $index));
        }

        return TransformationDataTypeService::GALLERY;

    }

    public function generateResultPreview($inputData)
    {
        if($inputData instanceof ImageGallery) {

            $items = [];

            foreach($inputData->getItems() as $item) {
                $items[] = 'GalleryImage: ' . ($item->getImage() ? $item->getImage()->getFullPath() : '');
            }

            return $items;
        }

        return $inputData;
    }

}