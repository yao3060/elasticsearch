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

## Apidoc

- Apidoc: https://apidocjs.com/
- API Doc example: app\controllers\LogController

## HTTP Status Code

http://gitlab.818ps.com/ips/elasticsearch/-/wikis/HTTP-Status-Code

## New Routes

## Templates

| ES Model             | Description          | New Route                    |
| -------------------- | -------------------- | ---------------------------- |
| ESTemplate           | 公共模版             | /818ps/v1/templates          |
| ESTemplateSecond     | 设计师模板，二次设计 | /818ps/v1/designer-templates |
| ESTemplateSinglePage | PPT 模板单页分类筛选 | /818ps/v1/ppt-templates      |
| ESTemplateExcerpt    | 片段视频             | /818ps/v1/video-templates    |

## Video

| ES Model          | Description | New Route                     |
| ----------------- | ----------- | ----------------------------- |
| ESVideoE          | 视频元素    | /818ps/v1/video-elements      |
| ESBgVideo         | 背景视频    | /818ps/v1/video-backgrounds   |
| ESVideoLottie     | 1           | /818ps/v1/videos/lottie       |
| ESVideoLottieWord | 1           | /818ps/v1/videos/lottie-words |

## 素材

| ES Model     | Description | New Route                    |
| ------------ | ----------- | ---------------------------- |
| ESAsset      | 素材        | /818ps/v1/assets             |
| ESGifAsset   | GIF         | /818ps/v1/gif-assets         |
| ESRtAsset    | 富文本元素  | /818ps/v1/rich-editor-assets |
| ESBackground | 背景图片    | /818ps/v1/backgrounds        |
| ESPicture    | 图片素材    | /818ps/v1/pictures           |
| ESVideoAudio | 试听素材    | /818ps/v1/audiovisuals       |
| ESGroupWord  | 组合字      | /818ps/v1/group-words        |
| ESSvg        | 获取 SVG    | /818ps/v1/svgs               |

## 关键词 ​

| ES Model                   | Description                        | New Route                    |
| -------------------------- | ---------------------------------- | ---------------------------- |
| ESSearchWord               | 关键词 ​                           | /818ps/v1/search-keywords    |
| ESSearchWordNew            | 更新关键词 ​                       | /818ps/v1/search-keywords    |
| ESBanWords                 | 敏感词                             | /818ps/v1/sensitive-words    |
| ESH5BanWords               | 敏感词                             | /818ps/v1/h5-sensitive-words |
| ESSeoSearchWord            | seo 词库中相关搜索词               | /818ps/v1/seo-keywords       |
| ESSeoSearchWordAsset       | seo 词库相关推荐                   | /818ps/v1/seo-keyword-assets |
| ESSeoDetailKeywordForTitle | 通过标题搜索关键词 ​               | /818ps/v1/seo-title-keywords |
| ESSeoNewPage               | seo 新版专题页面                   | /818ps/v1/seo/               |
| ESSeoLinkWord              | ESSeoLinkWord::similarQueryKeyword | /818ps/v1/seo/               |

## 裁剪

| ES Model    | Description | New Route            |
| ----------- | ----------- | -------------------- |
| ESContainer | 裁剪        | /818ps/v1/containers |
