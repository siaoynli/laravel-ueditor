<?php
/*
* @Author: hzwlxy
* @Email: 120235331@qq.com
* @Github: http：//www.github.com/siaoynli
* @Date: 2019/7/16 14:23
* @Version:
* @Description:
*/

namespace Siaoynli\LaravelUEditor;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Siaoynli\Image\Facades\Image;
use Siaoynli\Upload\Facades\Upload;

class  UEditorController extends Controller
{
    private $output;


    public  function  test(){
        return  view("ueditor");
    }

    public function serve(Request $request)
    {
        $action = $request->get('action', "config");
        switch ($action) {
            case "config":
                $result = config('ueditor.upload');
                break;
            case 'uploadimage':
                $result = $this->upload('image');
                break;
            case 'uploadfile':
                $result = $this->upload('attach');
                break;
            case 'uploadvideo':
                $result = $this->upload('video');
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => config('ueditor.upload.scrawlPathFormat', '/uploads/image'),
                    "maxSize" => config('ueditor.upload.scrawlMaxSize', 1024 * 1024 * 5),
                    "oriName" => "scrawl.png"
                );
                $fieldName = config('ueditor.upload.scrawlFieldName', 'upfile');
                $result = $this->uploadBase64($config, $fieldName);
                break;
            case 'listimage':
                $config = array(
                    'allowFiles' => config('ueditor.upload.imageManagerAllowFiles', [".png", ".jpg", ".jpeg", ".gif", ".bmp"]),
                    'listSize' => config('ueditor.upload.imageManagerListSize', 20),
                    "pathFormat" => config('ueditor.upload.imageManagerListPath', '/uploads/image')
                );
                $result = $this->listFile($config);
                break;
            /* 列出文件 */
            case 'listfile':
                $config = array(
                    'allowFiles' => config('ueditor.upload.fileManagerAllowFiles', [".rar", ".zip", ".txt", ".pdf"]),
                    'listSize' => config('ueditor.upload.fileManagerListSize', 20),
                    "pathFormat" => config('ueditor.upload.fileManagerListPath', '/uploads/attach')
                );
                $result = $this->listFile($config);
                break;
            case 'catchimage':
                $config = array(
                    "pathFormat" => config('ueditor.upload.scrawlPathFormat', '/uploads/image'),
                    "oriName" => "remote.png",
                    'allowFiles' => ["png", "jpg", "jpeg", "gif", "bmp"],
                    "maxSize" =>  1024 * 1024 * 5,
                );
                $fieldName =config('ueditor.upload.catcherFieldName', 'source');
                $result = $this->saveRemote($config, $fieldName);
                break;
            default:
                $result = array(
                    'state' => '请求地址出错'
                );
                break;
        }

        if ($request->get('callback', false)) {
            if (preg_match("/^[\w_]+$/", $request->get('callback'))) {
                $this->output = htmlspecialchars($request->get('callback')) . '(' . $result . ')';
            } else {
                $this->output = array(
                    'state' => 'callback参数不合法'
                );
            }
        } else {
            $this->output = $result;
        }
        return $this->output;
    }


    /**
     * @Author: hzwlxy
     * @Email: 120235331@qq.com
     * @Date: 2019/7/16 16:17
     * @Description:上传文件
     * @param $type
     * @return mixed
     */
    private function upload($type)
    {
        $info = Upload::type($type)->do('upfile');
        if ($type == "image") {
            if ($info["state"] == "SUCCESS") {
                $width=config('ueditor.upload.imageCompressBorder', 800);
                Image::file('.'.$info['url'])->resize($width)->save();
            }
            return $info;
        } else {
            return $info;
        }
    }

    /**
     * @Author: hzwlxy
     * @Email: 120235331@qq.com
     * @Date: 2019/7/16 16:18
     * @Description:涂鸦
     * @param $config
     * @param $fieldName
     * @return array|false|string
     */
    private function uploadBase64($config, $fieldName)
    {
        $result = array();
        $base64Data = request()->post($fieldName);
        $imgContent = base64_decode($base64Data);
        if (strlen($imgContent) > $config['maxSize']) {
            $result['states'] = '文件大小超过限制';
            return $result;
        }

        $filename = $config['pathFormat'] . '/' . date('Y-m-d') . '/' . sha1(uniqid()) . '.jpg';

        if ($this->put($filename, $imgContent)) {
            $result = array(
                'state' => 'SUCCESS',
                'url' => $filename,
                'title' => basename($filename),
                'original' => basename($filename),
                'type' => '.png',
                'size' => strlen($imgContent),
            );
        } else {
            $result = array(
                'state' => $config['pathFormat'] . '不可写',
            );
        }
        return $result;
    }

    private function put($filename, $content, $maxSize = -1, $cover = TRUE)
    {
        $filename = public_path($filename);

        if ($maxSize != -1) {
            if (strlen($content > $maxSize)) {
                return '文件大小超过限制';
            }
        }
        $dir = dirname($filename);
        if (!is_dir($dir))
            mkdir($dir, 0755, true);
        if (file_put_contents($filename, $content) === false) {
            return false;
        }
        return true;
    }


    private function listFile($config)
    {
        $allowFiles = substr(str_replace(".", "|", join("", $config['allowFiles'])), 1);
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $config['listSize'];
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        $path = public_path($config['pathFormat']);

        $files = $this->listAllFile($path, $allowFiles);

        if (!count($files)) {
            return array(
                "state" => "没有找到任何图片",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            );
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
            $list[] = $files[$i];
        }

        /* 返回数据 */
        $result = array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        );

        return $result;
    }


    private function listAllFile($path, $allowFiles = 'all')
    {
        return $this->getList($path, $allowFiles);
    }

    private function getList($path, $allowFiles = 'all', &$files = array())
    {
        if (!is_dir($path)) return [];
        if (substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getList($path2, $allowFiles, $files);
                } else {
                    if ($allowFiles != 'all') {
                        if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {

                            $files[] = array(
                                'url' => '/' . str_replace('\\', '/', substr($path2, strlen(public_path('/')))),
                                'mtime' => filemtime($path2)
                            );
                        }
                    } else {
                        $files[] = array(
                            'url' => '/' . str_replace('\\', '/', substr($path2, strlen(public_path('/')))),
                            'mtime' => filemtime($path2)
                        );
                    }
                }
            }
        }

        return $files;
    }


    private function saveRemote($config, $fieldName)
    {
        $list = array();
        if (request()->post($fieldName,null)) {
            $source =request()->post($fieldName);
        } else {
            $source = request()->get($fieldName);
        }

        foreach ($source as $imgUrl) {

            $imgUrl = htmlspecialchars($imgUrl);
            $imgUrl = str_replace("&amp;", "&", $imgUrl);
            //http开头验证
            if (strpos($imgUrl, "http") !== 0) {
                return  array('state' => '不是http链接');
            }
            $heads = get_headers($imgUrl);

            //死链检测
            if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                return array('state' => '不是有效连接！');

            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = ltrim(strtolower(strrchr($imgUrl, '.')), '.');

            if (!in_array($fileType, $config['allowFiles'])) {
               return  array("state" => "错误文件格式");
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array('http' => array(
                    'follow_location' => false // don't follow redirects
                ))
            );
            readfile($imgUrl, false, $context);
            $img = ob_get_contents();
            ob_end_clean();
            preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

            if (strlen($img) > $config['maxSize']) {
                $data['states'] = 'too large';
                return $data;
            }

            $path = $config['pathFormat'].'/'.date("Y-m-d");

            $filename =$path.'/'.sha1(uniqid()) . '.jpg';

            $oriName = $m ? $m[1] : "";


            if ($this->put($filename, $img)) {
                array_push($list, array(
                    "state" => 'SUCCESS',
                    "url" => $filename,
                    "size" => strlen($img),
                    "title" => basename($filename),
                    "original" => $oriName,
                    "source" => htmlspecialchars_decode($imgUrl)
                ));

                $data = array(
                    "url" =>  $filename,
                    "size" => strlen($img),
                    "title" => basename($filename),
                    "original" => $oriName,
                );

            } else {
                array_push($list, array('state' => '文件写入失败'));
            }
        }
        /* 返回抓取数据 */
        return array(
            'state' => count($list) ? 'SUCCESS' : 'ERROR',
            'list' => $list
        );
    }

}
