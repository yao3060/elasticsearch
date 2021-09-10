## TemplateController

### search 方法

<table align="center">
    <tr>
        <th>model</th> 
        <th>route</th> 
        <th>new api</th> 
        <th>unit test method</th> 
        <th>old api</th> 
   </tr>
    <tr>
        <td rowspan="2">Template</td>
        <td rowspan="2">公共模板</td>
        <td>v1/templates</td>
        <td>
          <p>TemplateTest@testSearch</p>
          <p>TemplateTest@testSearchCarryKeyword</p>
        </td>
        <td>
          <p><span style="color: #3d7eff;">[@testSearch]</span> /apiv2/get-ppt-template-list?sort_type=bytime</p>
          <p><span style="color: #e74c3c;">[@testSearchCarryKeyword] </span> /api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208</p>
        </td>
    </tr>
    <tr>
      <td>v1/templates/recommends</td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td rowspan="2">BackgroundVideo</td>
      <td rowspan="2">背景视频</td>
      <td rowspan="2">v1/background/videos</td>
      <td>BackgroundVideoTest@testSearch</td>
      <td><span style="color: #3d7eff;">[@testSearch]</span> /h5-api/bg-video-search</td>
    </tr>
    <tr>
      <td>BackgroundVideo@testVideoSearch</td>
      <td><span style="color: #e74c3c;">[@testVideoSearch]</span> /video/bg-video-search</td>
    </tr>
    <tr>
      <td>SensitiveWord</td>
      <td>敏感词</td>
      <td>v1/sensitive/word/validate</td>
      <td>SensitiveWordTest@testVideoSearch</td>
      <td><span style="color: #3d7eff">[@testVideoSearch]</span> /video/bg-video-search</td>
    </tr>
</table>
