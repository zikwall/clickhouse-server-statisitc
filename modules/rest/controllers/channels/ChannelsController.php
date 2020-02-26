<?php
namespace app\modules\rest\controllers\channels;

use app\modules\rest\components\BaseController;
use Yii;
use app\modules\statistic\models\MonitChannels;

class ChannelsController extends BaseController
{
    public function beforeAction($action): bool
    {
        set_time_limit(600);
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        return parent::beforeAction($action);
    }

    public function actionGetChannelsViewDuration()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $app = $request->post('app');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $data = MonitChannels::getChannelsViewDuration($app, $dayBegin, $dayEnd);
        $nameChannels = (array) json_decode(file_get_contents('https://pl.iptv2021.com/api/v1/channels?access_token=r0ynhfybabufythekbn'));

        /**
         * Приведение данных к нормальному виду и подстановка названий каналов + отсев мусора
         */

        foreach($data->rows as $key => $value) {
            if (!isset($nameChannels[$value['vcid']])){
                continue;
            }

            $channelsViewDuration[$nameChannels[$value['vcid']]]['vcid'] = $value['vcid'];
            $channelsViewDuration[$nameChannels[$value['vcid']]]['name'] = $nameChannels[$value['vcid']];
            $channelsViewDuration[$nameChannels[$value['vcid']]]['online'] = 0;
            $channelsViewDuration[$nameChannels[$value['vcid']]]['archive'] = 0;

            foreach($value['groupData'] as $key1 => $value1) {
                if ($value1[0] == 0) {
                    $channelsViewDuration[$nameChannels[$value['vcid']]]['online'] = $value1[1];
                }

                if ($value1[0] == 1) {
                    $channelsViewDuration[$nameChannels[$value['vcid']]]['archive'] = $value1[2];
                }
            }
        }

        return $this->asJson([
            'channelsViewDuration' => $channelsViewDuration
        ]);
    }

    public function actionGetStartChannels()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $app = $request->post('app');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $data = MonitChannels::getStartChannels($app, $dayBegin, $dayEnd);
        $nameChannels = (array) json_decode(file_get_contents('https://pl.iptv2021.com/api/v1/channels?access_token=r0ynhfybabufythekbn'));
        $startChannels = [];

        foreach($data->rows as $key => $value) {
            if (!isset($nameChannels[$value['vcid']])){
                continue;
            }

            $startChannels[$nameChannels[$value['vcid']]]['vcid'] = $value['vcid'];
            $startChannels[$nameChannels[$value['vcid']]]['name'] = $nameChannels[$value['vcid']];
            $startChannels[$nameChannels[$value['vcid']]]['ctn'] = $value['ctn'];
        }

        return $this->asJson([
            'startChannels' => $startChannels
        ]);
    }

    public function actionGetChannelsUniqUsers()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $app = $request->post('app');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $data = MonitChannels::getChannelsUniqUsers($app, $dayBegin, $dayEnd);

        return $this->asJson([
            'channelsUniqUsers' => $data
        ]);
    }

    public function actionGetStartApp()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $app = $request->post('app');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $data = MonitChannels::getStartApp($app, $dayBegin, $dayEnd);

        return $this->asJson([
            'startApp' => $data
        ]);
    }

    public function actionGetChefParameter()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $app = 'com.infolink.LimeHDTV';
        $dayBegin = 1567976400;
        $dayEnd = 1568149200;
        $data = MonitChannels::getChefParameter($app, $dayBegin, $dayEnd);

        return $this->asJson([
            'chefParameter' => $data
        ]);
    }

    public function actionGetChannelsUniqUsersWithEvtp()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $app = $request->post('app');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $nameChannels = (array) json_decode(file_get_contents('https://pl.iptv2021.com/api/v1/channels?access_token=r0ynhfybabufythekbn'));
        $data = MonitChannels::getContUsersWithEvtp($app, $dayBegin, $dayEnd);
        $channelsUniqUsersWithEvtp = [];

        //Приводим данные в нормальный вид + отсеиваем левые данные

        foreach ($data as $key => $item) {
            if (!isset($nameChannels[$item['vcid']])) {
                continue;
            }

            if (!isset($channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]][0])) {
                $channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]][0] = 0;
            }

            if (!isset($channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]][1])) {
                $channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]][1] = 0;
            }

            $channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]][$data[$key]['evtp']] = $item['ctn'];
            $channelsUniqUsersWithEvtp[$nameChannels[$item['vcid']]]['vcid'] = $item['vcid'];
        }

        return $this->asJson([
            'channelsUniqUsersWithEvtp' => $channelsUniqUsersWithEvtp
        ]);
    }

    public function actionGetStartAllApp()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $data = MonitChannels::getStartAllApp($dayBegin, $dayEnd);

        return $this->asJson([
            'startAllApp' => $data
        ]);
    }
        
    public function actionGetChannelsUniqUsersByAccount()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }
        
        $request = Yii::$app->request;
        $userChannels = $request->post('userChannels');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $userChannelsFormatedList = array_column($userChannels, 'name' ,'id');
        $userChannelsIds = array_keys($userChannelsFormatedList);
        $data = MonitChannels::getChannelsUniqUsersByAccount($userChannelsIds, $dayBegin, $dayEnd);
        $channelsUniqUsersByAccount = [];

        if (is_null($data)) {
            return null;
        }
        
        foreach ($data as $key => $item) {
            $channelsUniqUsersByAccount[$item['vcid']]['name'] = $userChannelsFormatedList[$item['vcid']];
            $channelsUniqUsersByAccount[$item['vcid']]['vcid'] = $item['vcid'];
            $channelsUniqUsersByAccount[$item['vcid']]['cnt'] = $item['cnt'];
        }
        
        return $this->asJson([
            $channelsUniqUsersByAccount
        ]);
    }
    
    public function actionGetChannelsViewDurationWithChannelsId()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }
        
        $request = Yii::$app->request;
        $userChannels = $request->post('userChannels');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        $userChannelsFormatedList = array_column($userChannels, 'name', 'id');
        $userChannelsIds = array_keys($userChannelsFormatedList);
        $data = MonitChannels::getChannelsViewDurationWithChannelsId($userChannelsIds, $dayBegin, $dayEnd);
        $channelsData = [];
        
        if (is_null($data)) {
            return null;
        }
        
        foreach ($data as $key => $item) {
            
            $channelsData[$item['vcid']]['name'] = $userChannelsFormatedList[$item['vcid']];
            $channelsData[$item['vcid']]['online'] = 0;
            $channelsData[$item['vcid']]['archive'] = 0;
            
            foreach ($item['groupData'] as $groupData) {
                if ($groupData[0] == 0) {
                    $channelsData[$item['vcid']]['online'] = $groupData[1];
                }
                
                if ($groupData[0] == 1) {
                    $channelsData[$item['vcid']]['archive'] = $groupData[2];
                }
            }
        }
        
        return $this->asJson([
            $channelsData
        ]);
    }
    
    public function actionGetStartChannelsOfPartner()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }
        
        $request = Yii::$app->request;
        $userChannels = $request->post('userChannels');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');
        
        if (is_null($userChannels)) {
            return false;
        }
        
        $startChannels = [];
        
        $userChannelsFormatedList = array_column($userChannels, 'name', 'id');
        $userChannelsIds = array_keys($userChannelsFormatedList);
        $data = MonitChannels::getStartChannelsOfPartner($userChannelsIds, $dayBegin, $dayEnd);
        
        foreach ($data->rows as $key => $item) {
            $startChannels[$item['vcid']]['name'] = $userChannelsFormatedList[$item['vcid']];
            $startChannels[$item['vcid']]['online'] = $item['cnt'];
        }
        
        return $this->asJson($startChannels);
    }
    
    public function actionGetChannelsByGadgetTypes()
    {
        if (Yii::$app->request->getIsOptions()) {
            return true;
        }

        $request = Yii::$app->request;
        $userChannels = $request->post('userChannels');
        $dayBegin = $request->post('dayBegin');
        $dayEnd = $request->post('dayEnd');

        if (is_null($userChannels)) {
            return false;
        }

        $startChannels = [];

        $userChannelsFormatedList = array_column($userChannels, 'name', 'id');
        $userChannelsIds = array_keys($userChannelsFormatedList);
        
        
        $groups = [
            'mobile' => [
                //android
                'com.infolink.limeiptv',
                'limehd.ru.lite',
                'limehd.ru.ctv',
                //ios
                'com.infolink.LimeHDTV',
                'liteios',
                'ctvios',
            ],
            'smarttv' => [
                'ru.limelime.NetCast',
                'limehd.ru.ctvshka',
                'ru.limelime.Tizen',
                'ru.limelime.webOs',
            ],
            'web' => [
                'web'
            ]
        ];

        $data = MonitChannels::getChannelsByGadgetTypes($userChannelsIds, $dayBegin, $dayEnd);
        $channelGadgets = [];

        foreach ($data->rows as $key => $item) {
            if (in_array($item['app'], $groups['mobile'])) {
                if (!isset($channelGadgets[$item['vcid']]['mobile'])) {
                    $channelGadgets[$item['vcid']]['mobile'] = $item['cnt'];
                } else {
                    $channelGadgets[$item['vcid']]['mobile'] += $item['cnt'];
                }
            }
            if (in_array($item['app'], $groups['smarttv'])) {
                if (!isset($channelGadgets[$item['vcid']]['smarttv'])) {
                    $channelGadgets[$item['vcid']]['smarttv'] = $item['cnt'];
                } else {
                    $channelGadgets[$item['vcid']]['smarttv'] += $item['cnt'];
                }
            }
            if (in_array($item['app'], $groups['web'])) {
                if (!isset($channelGadgets[$item['vcid']]['web'])) {
                    $channelGadgets[$item['vcid']]['web'] = $item['cnt'];
                } else {
                    $channelGadgets[$item['vcid']]['web'] += $item['cnt'];
                }
            }
            $channelGadgets[$item['vcid']]['name'] = $userChannelsFormatedList[$item['vcid']];
        }
        
        return $this->asJson($channelGadgets);
    }
}

