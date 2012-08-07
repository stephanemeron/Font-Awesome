<?php

class dmWidgetContentGalleryView extends dmWidgetPluginView
{
  
  public function configure()
  {
    parent::configure();
    
    $this->addRequiredVar(array('medias', 'method', 'animation'));

    if (dmConfig::get('site_theme_version') == 'v1'){
      $this->addJavascript(array('dmWidgetGalleryPlugin.view', 'dmWidgetGalleryPlugin.cycle'));
    } else {
      $this->addJavascript('/theme/less/bootstrap/js/bootstrap-carousel.js');
    }

  }

  protected function filterViewVars(array $vars = array())
  {
    $vars = parent::filterViewVars($vars);
    
    // extract media ids
    $mediaIds = array();
    foreach($vars['medias'] as $index => $mediaConfig)
    {
      $mediaIds[] = $mediaConfig['id'];
    }
    
    // fetch media records
    $mediaRecords = empty($mediaIds) ? array() : $this->getMediaQuery($mediaIds)->fetchRecords()->getData();
    
    // sort records
    $this->mediaPositions = array_flip($mediaIds);
    usort($mediaRecords, array($this, 'sortRecordsCallback'));
    
    // build media tags
    $medias = array();
    foreach($mediaRecords as $index => $mediaRecord)
    {
      $mediaTag = $this->getHelper()->media($mediaRecord);
  
      if (!empty($vars['width']) || !empty($vars['height']))
      {
        $mediaTag->size(dmArray::get($vars, 'width'), dmArray::get($vars, 'height'));
      }
  
      $mediaTag->method($vars['method']);
  
      if ($vars['method'] === 'fit')
      {
        $mediaTag->background($vars['background']);
      }
      
      if ($alt = $vars['medias'][$index]['alt'])
      {
        $mediaTag->alt($this->__($alt));
      }
      
      if ($quality = dmArray::get($vars, 'quality'))
      {
        $mediaTag->quality($quality);
      }
      
      $medias[] = array(
        'tag'   => $mediaTag,
        'link'  => $vars['medias'][$index]['link']
      );
    }
  
    // replace media configuration by media tags
    $vars['medias'] = $medias;
    
    return $vars;
  }
  
  protected function sortRecordsCallback(DmMedia $a, DmMedia $b)
  {
    return $this->mediaPositions[$a->get('id')] > $this->mediaPositions[$b->get('id')];
  }
  
  protected function getMediaQuery($mediaIds)
  {
    return dmDb::query('DmMedia m')
    ->leftJoin('m.Folder f')
    ->whereIn('m.id', $mediaIds);
  }

  protected function doRender()
  {
    if ($this->isCachable() && $cache = $this->getCache())
    {
      return $cache;
    }
    
    $vars = $this->getViewVars();
    $helper = $this->getHelper();
    
    if (dmConfig::get('site_theme_version') == 'v1'){

      $html = $helper->open('ol.dm_widget_content_gallery.list', array('json' => array(
        'animation' => $vars['animation'],
        'delay'     => dmArray::get($vars, 'delay', 3)
      )));
      
      foreach($vars['medias'] as $media)
      {
        $html .= $helper->tag('li.element', $media['link']
        ? $helper->link($media['link'])->text($media['tag'])
        : $media['tag']
        );
      }
      
      $html .= '</ol>';
    } else {
      $html = '';
      if (count($vars['medias'])){
        $items = '';
        foreach($vars['medias'] as $media)
        {
          $items .= $helper->tag('div.item', $media['link']
          ? $helper->link($media['link'])->text($media['tag'])
          : $media['tag']
          );
        }

        $html = '<div id="myCarousel" class="carousel slide">
        <!-- Carousel items -->
        <div class="carousel-inner">'

        .$items.    

        '</div>
        <!-- Carousel nav -->
        <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
        <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
        </div>';




        $html .= 

        "<script>
                      $(document).ready(function(){
                          $(\".carousel\").carousel(
                          {
                            interval: 500
                          }
                            );
                      });
                  </script>";

      } 

      
    }








    
    if ($this->isCachable())
    {
      $this->setCache($html);
    }
    
    return $html;
  }
  
  protected function doRenderForIndex()
  {
    $alts = array();
    foreach($this->compiledVars['medias'] as $media)
    {
      if (!empty($media['alt']))
      {
        $alts[] = $media['alt'];
      }
    }
    
    return implode(', ', $alts);
  }
  
}