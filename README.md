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

## Routes

```
ESTemplate	        (公共模版)               /818ps/v1/templates
# ESTemplateNew	      (用于替换公共模版)        /818ps/v2/templates
ESTemplateSecond	  (设计师模板，二次设计)     /818ps/v3/templates

ESBackground	      (背景图片)
ESAsset             （素材）             /818ps/v1/assets
ESPicture	          (图片素材)
ESSeoSearchWord	    (SEO搜索词)
ESVideoAudio	      (试听素材)

ESSearchWord	(9)
ESVideoE                              /818ps/v1/videos/elements

ESContainer	        (裁剪)
ESBanWords	(5)
ESBgVideo	(4)
ESSeoSearchWordAsset	(4)
ESSvg	(3)
ESSearchWordNew	    (不确定)
ESSeoDetailKeywordForTitle	(3)
ESGifAsset	(2)
ESSeoNewPage	(2)
ESGroupWord	(1)
ESH5BanWords	(1)
ESRtAsset	(1)
ESSeoLinkWord	(1)
ESVideoLottie	(1)
ESTemplateExcerpt	(1)
ESVideoLottieWord	(1)
ESTemplateSinglePage	(？？)
```
