<?php
namespace farawayslv\taggallery;

use Yii;
use yii\base\Widget;
use yii\data\Pagination;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;
use yii\helpers\Html;

class GalleryTag extends Widget
{    
    // string, optional parameter
    public $title = ''; 

    // string, optional parameter
    public $description = ''; 

    // string, required parameter. Text content with <gallery> tags
    public $content; 

    /* boolean, optional parameter. 
    * if  - true, we get images from all child folders, 
    * else  - only from root folder
    */
    public $isDeep = true;

    // integer, optional parameter. Haw many photos we want to display on page
    public $pageSize;

    // string, optional parameter
    public $imageClass = 'img-fluid';

    // string, optional parameter
    public $imageWrapClass = '';

    // integer, optional parameter. Haw many photos we want to display on one row 1-12
    public $inRow;

    // array options for LinkPager see Yii2 docs. https://www.yiiframework.com/doc/api/2.0/yii-widgets-linkpager
    public $pagerOptions = [];

    public function init()
    {
        parent::init();
        
        \farawayslv\taggallery\GalleryAsset::register($this->view);
        
        
        
        if(!is_int($this->pageSize) || $this->pageSize < 1) 
        {
            $this->pageSize = 24;
        }

        if(!$this->inRow || !is_int($this->inRow) || $this->inRow < 1)
        {
            $this->inRow = 6;
        }

        $col = ceil(12 / $this->inRow);
        
        $this->imageWrapClass .= ' col-sm-6 col-md-3 col-lg-'.$col;

    }

    public function run()
    {  
        if($this->content && $this->content != '') 
        {
            $this->renderGallery();
        }
    }

    private function renderContentBlock()
    {
        $newContent = '';
        preg_match_all('#<gallery>(.+?)</gallery>#is', $this->content, $sources);

        $texts = preg_split('#<gallery>(.+?)</gallery>#is', $this->content);
        for ($i=0; $i < count($texts); $i++) { 
            if($texts[$i] && trim($texts[$i]) != '')
            {
                $newContent .= $this->renderTextBlock($texts[$i]);
            }
            if($sources[1][$i] && trim($sources[1][$i]) != '')
            {
                $newContent .= $this->renderImageBlock($sources[1][$i]);
            }
        }
        return  $newContent;
    }

    private function renderTextBlock($text)
    {
        if($text && trim($text) != '')
        {
            return Html::tag('div', 
                Html::tag('p', $text),
                ['class'=>'row text-justify']
            );
        }
        return '';
    }
    
    private function renderImageBlock($path)
    {
        $files = $this->parseDir($path);
        
        if($files && count($files) > 0)
        {
            $pageParam = $this->createPageParams($path);

            $pages = new Pagination([
                'totalCount' => count($files), 
                'pageSize'=>$this->pageSize, 
                'pageParam' => $pageParam
                ]);

            $files = array_slice($files, $pages->offset, $pages->limit);          
            
            $options = [
                'pagination' => $pages
            ];
    
            if(is_array($this->pagerOptions) && count($this->pagerOptions) > 0)
            {
                $options['options'] = $this->pagerOptions;
            }
            
            
            
            $images = [];
            foreach ($files as $file) 
            {
                $images[] = Html::tag(
                    'div', 
                    Html::a(
                        Html::img($file, ['class' => $this->imageClass]), $file, ['data-lightbox' => 'images']
                    ), 
                        ['class' => $this->imageWrapClass.' item']
                    );
            }

            if(count($images) > 0) {
                return Html::tag('div', implode("\n", [
                        Html::tag('div', implode("\n", array_filter($images)), ['class' => 'row images']),
                        Html::tag('div', 
                            Html::tag('div', 
                                LinkPager::widget($options), 
                                ['class' => 'container d-flex justify-content-center']
                            ),
                            ['class' => 'row']
                        )
                        ]
                    )
                );
            }
        }
        return '';
    }

    private function renderGallery()
    {
        
        Pjax::begin(['timeout' => 5000, 'enablePushState' => false, 'class' => 'gallery-container']);

        echo Html::tag('div', 
                Html::tag('div', implode("\n", [
                    Html::tag('div', implode("\n", [
                        Html::tag('h2', Html::encode($this->title), ['class' => 'text-center']),
                        Html::tag('p', Html::encode($this->description), ['class' => 'text-center']),
                    ]),
                    ['class' => 'intro']),
                    $this->renderContentBlock($this->content),
                    ]),
                    ['class' => 'container']
                ),
                ['class' => 'image-gallery']
            );
        
        Pjax::end(); 
    }

    private function parseDir($dir, &$results = [])
    {
        $rootPath = Yii::getAlias('@webroot');
        
        if(file_exists($rootPath.$dir)) 
        {
            $files = scandir($rootPath.$dir);

            foreach($files as $key => $value)
            {
                $path = $dir.DIRECTORY_SEPARATOR.$value;
                if(!in_array($value, ['.', '..'])) 
                {
                    if(is_dir($rootPath.$path)) 
                    {
                        if($this->isDeep) 
                        {
                            $this->parseDir($path, $results);
                        }
                    } 
                    else 
                    {
                        $fileData = pathinfo($path);
                        if(in_array($fileData['extension'], ['png', 'jpg'])) 
                        {
                            $results[] = Yii::$app->request->baseUrl.$path;
                        }
                    }
                }
            }
        }
    
        return $results;
    }

    private function createPageParams($path)
    {
        $tmp = mb_strtolower($path);

        $tmp = explode('/', trim($path, '/'));
        
        $count = count($tmp);

        if($count > 2) 
        {
            $tmp = array_slice((array)$tmp, $count-2, 2);
        }

        return implode('-', (array)$tmp);
    }
}
    