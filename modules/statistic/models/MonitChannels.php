<?php
namespace app\modules\statistic\models;

use app\modules\clickhouse\models\CHBaseModel;
use app\modules\rest\helpers\DataHelper;
use Tinderbox\ClickhouseBuilder\Query\Builder;
use Tinderbox\ClickhouseBuilder\Query\From;

class MonitChannels extends CHBaseModel
{
    public static function getChannelsViewDuration($app, $dayBegin, $dayEnd)
    {
        $query = self::find()
            ->select([
                'vcid',
                raw('groupArray([toString(evtp), toString(ctnarch), toString(ctnonline)]) as groupData')
            ])
            ->from(function (From $from) use ($app, $dayBegin, $dayEnd) {
                $from = $from->query();

                $from
                    ->select([
                        'vcid',
                        'evtp',
                        raw('countIf(evtp = 0) as ctnarch'),
                        raw('countIf(evtp = 1) as ctnonline')
                    ])
                    ->from('stat')
                    ->where(function (Builder $query) use ($app) {
                        if (DataHelper::isAll($app)) {
                            $query->whereIn('app', MonitData::getApp(true));
                        } else {
                            $query->where('app', '=', $app);
                        }
                    })

                    ->where('day_begin', '>=', $dayBegin)
                    ->where('day_begin', '<=', $dayEnd)
                    ->where('adsst', '=', 'NULL')
                    ->where('action', '!=', 'opening-channel')
                    ->where('evtp', '!=', 666666)
                    ->groupBy(['vcid', 'evtp']);
            })
        ->groupBy(['vcid']);

        return self::execute($query);
    }

    public static function getStartChannels($app, $dayBegin, $dayEnd)
    {
        $query = self::find()
            ->select(['vcid', raw('COUNT(*) as ctn')])
            ->from('stat')
            ->where(function (Builder $query) use ($app) {
                if (DataHelper::isAll($app)) {
                    $query->whereIn('app', MonitData::getApp(true));
                } else {
                    $query->where('app', '=', $app);
                }
            })
            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->where('action', '=', 'opening-channel')
            ->groupBy(['vcid']);

        return self::execute($query);
    }

    public static function getChannelsUniqUsers($app, $dayBegin, $dayEnd)
    {
        $query = self::find()->select([
            'vcid',
            raw('COUNT(DISTINCT device_id) as ctn'),
        ])
            ->from('stat')
            ->where(function (Builder $query) use ($app) {
                if (DataHelper::isAll($app)) {
                    $query->whereIn('app', MonitData::getApp(true));
                } else {
                    $query->where('app', '=', $app);
                }
            })
            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->where('adsst', '=', 'NULL')
            ->where('evtp', '!=', 666666)
            ->groupBy('vcid');

        return self::execute($query);
    }

    public static function getStartApp($app, $dayBegin, $dayEnd)
    {
        $query = self::find()->select(['day_begin', raw('COUNT(*) as ctn')])->from('stat')
            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->where(function (Builder $query) use ($app) {
                /**
                 * Данные iOS и остальных платформ отличаются, поэтому дано добро на существование такого костыля
                 */
                if (MonitData::isNIkkitaVesdePobrita($app)) {
                    // iOS берется по полю action
                    $query
                        ->where('action', '=', 'start-app')
                        ->where('app', '=', $app);
                } elseif (DataHelper::isAll($app)) {
                    // тут вся гремуча смесь вместе
                    $query
                        ->where('window', '=', 'loading')
                        ->whereIn('app', MonitData::getApp(true))
                        ->orWhere('action', '=', 'start-app');
                } else {
                    // остальные платформы не отправляют action, но у них есть поле launch
                    $query
                        ->where('window', '=', 'loading')
                        ->where('app', '=', $app);
                }
            })->groupBy(['day_begin'])->orderBy('day_begin');

        return self::execute($query);
    }

    public static function getChefParameter($app, $dayBegin, $dayEnd)
    {
        $query = self::find()->select([
            'day_begin', raw('COUNT(DISTINCT device_id) as ctn')
        ])->from('stat')
            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->groupBy('day_begin')
            ->havingRaw('ctn > 0');

        if (DataHelper::isAll($app)) {
            $query->whereIn('app', MonitData::getApp(true));
        } else {
            $query->where('app', '=', $app);
        }

        return $query->toSql();
    }

    public static function getContUsersWithEvtp($app, $dayBegin, $dayEnd)
    {
        $query = self::find()
            ->select([
                'vcid',
                'evtp',
                raw('COUNT(DISTINCT device_id) as ctn')
            ])
            ->from('stat')
            ->where(function (Builder $query) use ($app) {
                if (DataHelper::isAll($app)) {
                    $query->whereIn('app', MonitData::getApp(true));
                } else {
                    $query->where('app', '=', $app);
                }
            })

            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->where('adsst', '=', 'NULL')
            ->where('evtp', '!=', 666666)
            ->groupBy(['vcid', 'evtp']);

        return self::execute($query);
    }

    public static function getStartAllApp($dayBegin, $dayEnd)
    {
        $query = self::find()->select(['app', raw('COUNT(*) as ctn')])->from('stat')
            ->where('day_begin', '>=', $dayBegin)
            ->where('day_begin', '<=', $dayEnd)
            ->where('action', '=', 'start-app')
            ->whereIn('app', MonitData::getApp(true))
            ->groupBy(['app']);
        
            return self::execute($query);
    }
    
    public static function getChannelsUniqUsersByAccount($userChannels, $dayBegin, $dayEnd)
    {
        $query = self::find()
                ->select([
                    'vcid',
                    raw('COUNT(DISTINCT device_id) as cnt')
                ])
                ->from('stat')
                ->whereIn('vcid', $userChannels)
                ->where('day_begin', '>=', $dayBegin)
                ->where('day_begin', '<=', $dayEnd)
                ->where('adsst', '=', 'NULL')
                ->where('evtp', '!=', 666666)
                ->groupBy(['vcid']);
        
        return self::execute($query);
    }
    
    public static function getChannelsViewDurationWithChannelsId($userChannels, $dayBegin, $dayEnd)
    {
        $query = self::find()
                ->select([
                    'vcid',
                    raw('groupArray([toString(evtp), toString(ctnarch), toString(ctnonline)]) as groupData')
                ])
                ->from(function (From $from) use ($userChannels, $dayBegin, $dayEnd) {
                    $from = $from->query();

                    $from
                    ->select([
                        'vcid',
                        'evtp',
                        raw('countIf(evtp = 0) as ctnarch'),
                        raw('countIf(evtp = 1) as ctnonline')
                    ])
                    ->from('stat')
                    ->whereIn('vcid', $userChannels)
                    ->where('day_begin', '>=', $dayBegin)
                    ->where('day_begin', '<=', $dayEnd)
                    ->where('adsst', '=', 'NULL')
                    ->where('action', '!=', 'opening-channel')
                    ->where('evtp', '!=', 666666)
                    ->groupBy(['vcid', 'evtp']);
                })
                ->groupBy(['vcid']);

        return self::execute($query);
    }
    
    public static function getStartChannelsOfPartner($userChannels, $dayBegin, $dayEnd)
    {
        $query = self::find()
                ->select(['vcid', raw('COUNT(*) as cnt')])
                ->from('stat')
                ->whereIn('vcid', $userChannels)
                ->where('day_begin', '>=', $dayBegin)
                ->where('day_begin', '<=', $dayEnd)
                ->where('action', '=', 'opening-channel')
                ->groupBy(['vcid']);

        return self::execute($query);
    }
    
    public static function getChannelsByGadgetTypes($userChannels, $dayBegin, $dayEnd)
    {
        $query = self::find()
                ->select(['vcid','app', raw('COUNT(DISTINCT device_id) as cnt')])
                ->from(function (From $from) use ($userChannels, $dayBegin, $dayEnd) {
                    $from = $from->query();
                           
                    $from
                        ->select([
                            'vcid',
                            'app',
                            'device_id'
                        ])
                        ->from('stat')
                        ->whereIn('vcid', $userChannels)
                        ->where('day_begin', '>=', $dayBegin)
                        ->where('day_begin', '<=', $dayEnd)
                        //->where('month_begin', '>=', strtotime(date('Y-m-01', $dayBegin)))
                    ->groupBy(['vcid', 'app', 'device_id']);
                })
                ->groupBy(['vcid', 'app']);
                
        return self::execute($query);
    }
}