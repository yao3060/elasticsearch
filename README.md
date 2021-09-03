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

```
ESTemplate	        (公共模版)               /818ps/v1/templates
# ESTemplateNew	    (用于替换公共模版)        /818ps/v2/templates
ESTemplateSecond	  (设计师模板，二次设计)     /818ps/v3/templates
ESTemplateExcerpt	  (获取片段视频)
ESTemplateSinglePage(PPT模板单页分类筛选)     /

ESBackground	      (背景图片)
ESAsset             (素材)                  /818ps/v1/assets
ESPicture	          (图片素材)               /818ps/v1/pictures
ESVideoAudio	      (试听素材)               /818ps/v1/audiovisuals

ESSearchWord	      (问苏企)                 /818ps/v1/search-words
ESSearchWordNew	    (问苏企)                 /818ps/v1/search-words
ESVideoE            (视频元素)               /818ps/v1/video-elements
ESBgVideo	          (背景视频)               /818ps/v1/background-videos

ESContainer	        (裁剪)                   /818ps/v1/containers

ESGroupWord	        (组合字)                 /818ps/v1/group-words
ESBanWords	        (5)                     /818ps/v1/ban-words
ESH5BanWords	      (1)                     /818ps/v1/h5/ban-words

ESSeoSearchWord	    (搜索模块搜索词)          /818ps/v1/seo/search-words
ESSeoSearchWordAsset	(4)                   /818ps/v1/seo/search-words/assets
ESSeoDetailKeywordForTitle	(3)             /
ESSeoNewPage	(2)                           /818ps/v1/seo/
ESSeoLinkWord	(1)                           /818ps/v1/seo/

ESSvg	(3)                                   /818ps/v1/svgs
ESGifAsset	(2)                             /818ps/v1/gif/assets
ESRtAsset	(1)                               /818ps/v1/rt/assets
ESVideoLottie	(1)                           /818ps/v1/videos/lottie
ESVideoLottieWord	(1)                       /818ps/v1/videos/lottie-words
```
