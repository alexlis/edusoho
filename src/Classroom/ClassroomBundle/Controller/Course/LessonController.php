<?php
namespace Classroom\ClassroomBundle\Controller\Course;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Util\CloudClientFactory;
use Topxia\WebBundle\Controller\BaseController;

class LessonController extends BaseController
{

    public function previewAction(Request $request, $classroomId, $courseId)
    {
        $lessonId = $request->query->get('lessonId', 0);
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $user = $this->getCurrentUser();
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroom['id'], $user['id']) : null;

        if ($lesson['free'] || ($member && !$member['locked'])) {
            return $this->forward('TopxiaWebBundle:CourseLesson:preview', array(
                'courseId' => $courseId,
                'lessonId' => $lessonId
            ));
        }

        return $this->render('ClassroomBundle:Classroom:hint-modal.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function listAction(Request $request, $classroomId, $courseId)
    {
        $user = $this->getCurrentUser();
        $member = $user ? $this->getClassroomService()->getClassroomMember($classroomId, $user['id']) : null;
        return $this->render('ClassroomBundle:Classroom/Course:lessons-list.html.twig', array(
            'classroomId' => $classroomId,
            'courseId' => $courseId,
            'member' => $member
        ));
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}