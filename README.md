# Boilerplate Base

## Configure project name, environment and HTTP Hosts

```
vim .env.default
```

## Start project

```
./start
```

All `docker-compose up` options available in `./start`
Additional Options for `./start`

`--skip-install` to skip install dependencies

E.g.

`./start -d` to start in detach mode
`./start -d --force-recreate` to start in detach mode and recreate
`./start --skip-install -d` to start docker as daemon mode and skip modules installation

Start project with no-dev option - simulate to production environment

`./start --no-dev`

Or

`./docker-compose --no-dev up -d --force-recreate --remove-orphans` to start built docker container

## Stop project

```
./stop
```

> Note: Don't run stop unless you want to remove entries docker images and volumes inside your local machine
> Alternative way: can be done is run `./docker-compose down` to remove all docker containers, It will leave docker built images and next time `./docker-compose up -d` can be used to create and start containers again
> See `docker-guide.md` for more docker commands

## HTTP Status Code

http://gitlab.818ps.com/ips/elasticsearch/-/wikis/HTTP-Status-Code

## Routes

## Templates

```
[ ] ESTemplate	          (公共模版)               /818ps/v1/templates
[ ] ESTemplateSecond	  (设计师模板，二次设计)     /818ps/v1/designer-templates
[ ] ESTemplateSinglePage  (PPT模板单页分类筛选)     /818ps/v1/ppt-templates
[ ] ESTemplateExcerpt	  (片段视频)               /818ps/v1/video-templates
```

## Video

```
[ ] ESVideoE            (视频元素)                 /818ps/v1/video-elements
[ ] ESBgVideo	          (背景视频)                   /818ps/v1/video-backgrounds
[ ] ESVideoLottie	(1)                             /818ps/v1/videos/lottie
[ ] ESVideoLottieWord	(1)                         /818ps/v1/videos/lottie-words
```

## 素材

```
ESAsset                   (素材)                  /818ps/v1/assets
ESGifAsset	          (GIF)                  /818ps/v1/gif-assets
ESRtAsset	          (富文本元素)             /818ps/v1/rich-editor-assets
ESBackground	          (背景图片)               /818ps/v1/backgrounds
ESPicture	          (图片素材)               /818ps/v1/pictures
ESVideoAudio	          (试听素材)               /818ps/v1/audiovisuals
ESGroupWord	          (组合字)                /818ps/v1/group-words
ESSvg	                  (获取SVG)               /818ps/v1/svgs
```

## 关键词 ​

```
ESSearchWord	        (关键词​)                 /818ps/v1/search-keywords
ESSearchWordNew	        (更新关键词​)              /818ps/v1/search-keywords
ESBanWords	        (敏感词)                 /818ps/v1/sensitive-words
ESH5BanWords	        (敏感词)                 /818ps/v1/h5-sensitive-words

ESSeoSearchWord	                (seo词库中 相关搜索词)       /818ps/v1/seo-keywords
ESSeoSearchWordAsset	        (seo词库相关推荐)           /818ps/v1/seo-keyword-assets
ESSeoDetailKeywordForTitle	(通过标题搜索关键词​)         /818ps/v1/seo-title-keywords
ESSeoNewPage	(2)                               /818ps/v1/seo/
ESSeoLinkWord	(1)                               /818ps/v1/seo/
```

## 裁剪

```
ESContainer	        (裁剪)                   /818ps/v1/containers
```

```
[ ] ESTemplate	        (公共模版)               /818ps/v1/templates
[ ] ESTemplateSecond	  (设计师模板，二次设计)     /818ps/v1/designer-templates
[ ] ESTemplateSinglePage(PPT模板单页分类筛选)     /818ps/v1/ppt-templates
[ ] ESTemplateExcerpt	  (片段视频)               /818ps/v1/video-templates
ESVideoE            (视频元素)               /818ps/v1/video-elements
ESBgVideo	          (背景视频)               /818ps/v1/video-backgrounds

ESAsset             (素材)                  /818ps/v1/assets
ESBackground	      (背景图片)               /818ps/v1/backgrounds
ESPicture	          (图片素材)               /818ps/v1/pictures
ESVideoAudio	      (试听素材)               /818ps/v1/audiovisuals
ESGroupWord	        (组合字)                 /818ps/v1/group-words

ESContainer	        (裁剪)                   /818ps/v1/containers


ESSearchWord	      (关键词​)                 /818ps/v1/search-keywords
ESSearchWordNew	    (更新关键词​)              /818ps/v1/search-keywords
ESBanWords	        (敏感词)                 /818ps/v1/sensitive-words
ESH5BanWords	      (敏感词)                 /818ps/v1/h5-sensitive-words

ESSeoSearchWord	    (seo词库中 相关搜索词)         /818ps/v1/seo-keywords
ESSeoSearchWordAsset	(seo词库相关推荐)           /818ps/v1/seo-keywords/assets
ESSeoDetailKeywordForTitle	(通过标题搜索关键词​)   /818ps/v1/seo/
ESSeoNewPage	(2)                               /818ps/v1/seo/
ESSeoLinkWord	(1)                               /818ps/v1/seo/

ESSvg	(3)                                   /818ps/v1/svgs
ESGifAsset	(2)                             /818ps/v1/gif/assets
ESRtAsset	(1)                               /818ps/v1/rich-editor/assets
ESVideoLottie	(1)                           /818ps/v1/videos/lottie
ESVideoLottieWord	(1)                       /818ps/v1/videos/lottie-words
```
