<?php

namespace App\Controller\Backend;

use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UIElementsController
 *
 * @package App\Controller\Backend
 * @Route("/admin")
 */
class UIElementsController extends BaseController
{

    /**
     * @Route("/uielements/modals", name="uielements_modals")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modals()
    {
        return $this->render('backend/uielements/modals.html.twig');
    }

    /**
     * @Route("/uielements/tabs", name="uielements_tabs")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tabs()
    {
        return $this->render('backend/uielements/tabs.html.twig');
    }

    /**
     * @Route("/uielements/forms/basics", name="uielements_forms_basics")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function basicForms()
    {
        return $this->render('backend/uielements/forms_basics.html.twig');
    }

    /**
     * @Route("/uielements/forms/validation", name="uielements_forms_validation")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validationForms()
    {
        return $this->render('backend/uielements/forms_validation.html.twig');
    }

    /**
     * @Route("/uielements/forms/ckeditor", name="uielements_forms_ckeditor")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ckEditor()
    {
        return $this->render('backend/uielements/forms_ckeditor.html.twig');
    }

    /**
     * @Route("/uielements/forms/summernote", name="uielements_forms_summernote")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function summernote()
    {
        return $this->render('backend/uielements/forms_summernote.html.twig');
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $template   = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }
        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }

        return $errors;
    }
}