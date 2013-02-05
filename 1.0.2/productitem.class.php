<?php

    class productItem extends Object 
	{

		var $oDocument;
		var $oProduct;
		var $thumbnails = null;
		var $options = array();
		
		function setDocument($document_srl)
		{
			$oDocumentModel = &getModel('document');
			$this->setDocumentObject($oDocumentModel->getDocument($document_srl));
		}

		function setDocumentObject($oDocument)
		{
			$this->oDocument = $oDocument;
			if($this->oDocument)
			{
				foreach($this->oDocument->variables as $key=>$value)
					$this->add($key, $value);
			}
			
			$args->document_srl = $oDocument->document_srl;
			$output = executeQuery('product.getProduct', $args);

			$this->oProduct = $output->data;
			if($this->oProduct)
			{
				foreach($this->oProduct as $key=>$value)
					$this->add($key, $value);
			}
			
			$oFileModel = &getModel("file");
			if( $oFileModel->getFiles($this->oProduct->product_srl) )
			{
				$this->thumbnails = $oFileModel->getFiles($this->oProduct->product_srl);
			}
			
			$output = executeQuery('product.getProductOption', $args);
			$this->options = $output->data;
			if( count($output->data) == 1 ) $this->options = array($output->data);
		}

		function thumbnailExists($width, $height, $thumbnail_type)
		{
			if(!$this->oProduct->product_srl) return false;
            if(!$this->getThumbnail($width, $height, $thumbnail_type)) return false;
            return true;
		}

		function getThumbnail($width = 100, $height = 100, $thumbnail_type = '')
		{
			if(!$height) $height = $width;

			if(!$this->oProduct->master_image) return false;

            // 썸네일 정보 정의
            $thumbnail_path = sprintf('files/cache/thumbnails/%s',getNumberingPath($this->oProduct->product_srl, 2));
            $thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, $width, $height, $thumbnail_type);
            $thumbnail_url  = Context::getRequestUri().$thumbnail_file;

            // 대상 파일
            $source_file = null;
			$oFileModel = &getModel("file");
			$oFile = $oFileModel->getFile($this->oProduct->master_image);
			
            if($oFile){
				$source_file = $oFile->uploaded_filename;
				if( file_exists($thumbnail_file) )
				{
					// 썸네일 있을때
				}else{
					// 썸네일 없을때
					$source_file = $oFile->uploaded_filename;
					$output = FileHandler::createImageFile($source_file, $thumbnail_file, $width, $height, 'jpg', $thumbnail_type);
				}
            }
            // 썸네일 생성 성공시 경로 return
            return $thumbnail_url;
		}

		function itemThumbnailExists($index, $width, $height, $thumbnail_type)
		{
			if(!$this->thumbnails) return false;
            if(!$this->getItemThumbnail($index, $width, $height, $thumbnail_type)) return false;
            return true;
		}

		function getItemThumbnail($index, $width, $height, $thumbnail_type)
		{
			$itemImage = $this->thumbnails[$index];
			if(!$itemImage) return false;
			
            // 썸네일 정보 정의
            $thumbnail_path = sprintf('files/cache/thumbnails/%s[%s]', getNumberingPath($this->oProduct->product_srl, 2), $index);
            $thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, $width, $height, $thumbnail_type);
            $thumbnail_url  = Context::getRequestUri().$thumbnail_file;

			if(file_exists($thumbnail_file)) {
                if(filesize($thumbnail_file)<1) return false;
                else return $thumbnail_url;
            }
			
            // 대상 파일
            $source_file = null;
            $is_tmp_file = false;

			$oFileModel = &getModel("file");
			$oFile = $oFileModel->getFile($this->oProduct->master_image);
			
            if($itemImage){
				$source_file = $itemImage->uploaded_filename;
                $output = FileHandler::createImageFile($source_file, $thumbnail_file, $width, $height, 'jpg', $thumbnail_type);
            }
            if($is_tmp_file) FileHandler::removeFile($source_file);

            // 썸네일 생성 성공시 경로 return

            if($output) return $thumbnail_url;

            // 차후 다시 썸네일 생성을 시도하지 않기 위해 빈 파일을 생성
            else FileHandler::writeFile($thumbnail_file, '','w');

			return true;
		}

		function getItemThumbnailList($width, $height, $thumbnail_type)
		{
			$thumbnailOutput = null;
			foreach($this->thumbnails as $key => $value)
			{
				$thumbnailOutput[] = $this->getItemThumbnail($key, $width, $height, $thumbnail_type);
			}
			return $thumbnailOutput;
		}

    }
?>
