# aycors
thinkphp5 for cors

## 安装方法

### 第一步 使用composer下载SDK

```bash
composer require ayteam/aycors
```

### 第二步 项目根目录执行

```bash
php think cors:config
```

> 查看application/extra/cors.php这个文件是否存在，
如果不存在复制一份 [config.php](https://github.com/ayteam/aycors/blob/master/src/config.php) 
到 application/extra 

## 使用方法
具体参考 [跨域资源共享 CORS 详解](http://www.ruanyifeng.com/blog/2016/04/cors.html)

```php
return Cors::json(['code'=>1]); // 替代 return json(['code'=>1]);
```


