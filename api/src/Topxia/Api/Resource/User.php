<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{
    private $_unsetFields = array(
        'password', 'salt', 'payPassword', 'payPasswordSalt'
    );

    private $_publicFields = array(
        'id', 'nickname', 'title', 'roles', 'point', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'about', 'createdTime', 'updatedTime'
    );

    private $_privateFields = array(
        'id', 'nickname', 'title', 'tags', 'type', 'roles',
        'point', 'coin', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'about',
        'email', 'emailVerified', 'promoted', 'promotedTime', 'locked', 'lockDeadline',
        'loginTime', 'loginIp', 'approvalTime', 'approvalStatus', 'newMessageNum', 'newNotificationNum',
        'createdIp', 'createdTime', 'updatedTime'
    );

    private $_profileFields = array(
        'truename', 'idcard', 'gender', 'birthday', 'city', 'mobile', 'qq',
        'signature', 'about', 'company', 'job', 'school', 'class', 'weibo', 'weixin', 'site'
    );

    public function get(Application $app, Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            return $this->error(404, "用户(#{$id})不存在");
        }

        $user['profile'] = $this->getUserService()->getUserProfile($id);

        return $this->filter($user);
    }

    public function filter(&$res)
    {
        foreach ($this->_unsetFields as $key) {
            unset($res[$key]);
        }

        $res['about'] = $res['profile']['about'];

        $currentUser = getCurrentUser();

        $returnRes = array();

        if ($currentUser->isAdmin() || ($currentUser['id'] == $res['id'])) {
            foreach ($this->_privateFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            foreach ($this->_profileFields as $key) {
                $returnRes[$key] = $res['profile'][$key];
            }
        } else {
            foreach ($this->_publicFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            if (in_array('ROLE_TEACHER', $returnRes['roles'])) {
                $returnRes['roles'] = array('ROLE_TEACHER');
            } else {
                $returnRes['roles'] = array('ROLE_USER');
            }
        }

        $res = $returnRes;

        foreach (array('smallAvatar', 'mediumAvatar', 'largeAvatar') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        foreach (array('promotedTime', 'loginTime', 'approvalTime', 'createdTime') as $key) {
            if (!isset($res[$key])) {
                continue;
            }

            $res[$key] = date('c', $res[$key]);
        }

        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id']       = $res['id'];
        $simple['nickname'] = $res['nickname'];
        $simple['title']    = $res['title'];
        $simple['avatar']   = $this->getFileUrl($res['smallAvatar']);

        return $simple;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
