<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inquiry extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
    }

    /**
     * Public API endpoint to receive form submissions from marketing site
     * POST /inquiry/submit
     */
    public function submit() {
        // Enforce POST method
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Invalid request method. Only POST is accepted.'
            ], 405);
        }

        $form_type = $this->input->post('form_type', TRUE);
        if (empty($form_type)) {
            $form_type = 'contact';
        }

        switch ($form_type) {
            case 'admission':
                return $this->_handle_admission();

            case 'newsletter':
                return $this->_handle_newsletter();

            case 'contact':
            default:
                return $this->_handle_contact();
        }
    }

    private function _handle_contact() {
        $name         = trim($this->input->post('full_name', TRUE));
        $phone        = trim($this->input->post('phone', TRUE));
        $email        = trim($this->input->post('email', TRUE));
        $inquiry_type = trim($this->input->post('inquiry_type', TRUE));
        $campus       = trim($this->input->post('campus', TRUE));
        $tour_date    = trim($this->input->post('tour_date', TRUE));
        $message      = trim($this->input->post('message', TRUE));

        // Required validation
        if (empty($name) || empty($phone) || empty($email) || empty($message)) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Please fill in all required fields (Name, Phone, Email, Message).'
            ], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Please provide a valid email address.'
            ], 400);
        }

        // Campus name lookup
        $campus_names = [
            'mainland' => 'Lagos Mainland Flagship',
            'lekki'    => 'Victoria Island / Lekki Annex',
            'ikeja'    => 'Ikeja Campus'
        ];
        $campus_label = isset($campus_names[$campus]) ? $campus_names[$campus] : $campus;

        // Inquiry type lookup
        $inquiry_labels = [
            'tour'       => 'Book a Campus Tour',
            'admissions' => 'Admissions & Entry Inquiry',
            'fees'       => 'Tuition & Payment Plans',
            'boarding'   => 'Boarding House Inquiry',
            'general'    => 'General Feedback / Question'
        ];
        $type_label = isset($inquiry_labels[$inquiry_type]) ? $inquiry_labels[$inquiry_type] : $inquiry_type;

        $purpose_parts = [];
        if (!empty($type_label))   $purpose_parts[] = $type_label;
        if (!empty($campus_label)) $purpose_parts[] = 'Campus: ' . $campus_label;
        if (!empty($tour_date))    $purpose_parts[] = 'Tour Date: ' . $tour_date;
        $purpose_str = implode(' | ', $purpose_parts);

        $data = [
            'category'     => 'Campus Visit / General Inquiry',
            'mobile'       => $phone,
            'purpose'      => $purpose_str,
            'name'         => $name,
            'whom_to_meet' => 'Admissions & Front Desk',
            'email'        => $email,
            'content'      => $message,
            'status'       => 'new'
        ];

        $inserted = $this->db->insert('enquiry', $data);
        if (!$inserted) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Unable to save inquiry at this time. Please call us directly.'
            ], 500);
        }

        return $this->_json_response([
            'status'  => 'success',
            'message' => 'Thank you! Your inquiry has been received by Seeds of Knowledge Intl School. Our team will contact you within 24 hours.'
        ]);
    }

    private function _handle_admission() {
        $student_name    = trim($this->input->post('student_name', TRUE));
        $student_dob     = trim($this->input->post('student_dob', TRUE));
        $programme_level = trim($this->input->post('programme_level', TRUE));
        $campus_choice   = trim($this->input->post('campus_choice', TRUE));
        $parent_name     = trim($this->input->post('parent_name', TRUE));
        $parent_phone    = trim($this->input->post('parent_phone', TRUE));
        $parent_email    = trim($this->input->post('parent_email', TRUE));
        $parent_notes    = trim($this->input->post('parent_notes', TRUE));

        if (empty($student_name) || empty($student_dob) || empty($programme_level) || empty($parent_name) || empty($parent_phone) || empty($parent_email)) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Please complete all required application fields.'
            ], 400);
        }

        $prog_names = [
            'nursery' => 'Nursery & Early Years',
            'primary' => 'Primary School (Basic 1-6)',
            'jss'     => 'Junior Secondary (JSS1 - JSS3)',
            'sss'     => 'Senior Secondary (SSS1 - SSS3)'
        ];
        $prog_label = isset($prog_names[$programme_level]) ? $prog_names[$programme_level] : $programme_level;

        $campus_names = [
            'mainland' => 'Mainland Flagship',
            'lekki'    => 'Victoria Island / Lekki Annex',
            'ikeja'    => 'Ikeja Campus'
        ];
        $campus_label = isset($campus_names[$campus_choice]) ? $campus_names[$campus_choice] : $campus_choice;

        $purpose_str = 'Application for ' . $prog_label . ' (' . $campus_label . ')';
        $content_str = 'Child: ' . $student_name . ' (DOB: ' . $student_dob . ')' . "\n"
                     . 'Parent: ' . $parent_name . "\n"
                     . 'Notes: ' . ($parent_notes ? $parent_notes : 'None');

        $data = [
            'category'     => 'Online Admission Application',
            'mobile'       => $parent_phone,
            'purpose'      => $purpose_str,
            'name'         => $student_name . ' (Parent: ' . $parent_name . ')',
            'whom_to_meet' => 'Head of Admissions',
            'email'        => $parent_email,
            'content'      => $content_str,
            'status'       => 'new'
        ];

        $inserted = $this->db->insert('enquiry', $data);
        if (!$inserted) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Unable to submit application at this time.'
            ], 500);
        }

        return $this->_json_response([
            'status'  => 'success',
            'message' => 'Application received! Our Admissions Office will contact you with your assessment schedule and form reference number shortly.'
        ]);
    }

    private function _handle_newsletter() {
        $email = trim($this->input->post('email', TRUE));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->_json_response([
                'status'  => 'error',
                'message' => 'Please provide a valid email address.'
            ], 400);
        }

        $data = [
            'category'     => 'Newsletter Subscription',
            'mobile'       => 'N/A',
            'purpose'      => 'Website Newsletter',
            'name'         => 'Newsletter Subscriber',
            'whom_to_meet' => 'Media & Comms',
            'email'        => $email,
            'content'      => 'Subscribed from website news page.',
            'status'       => 'new'
        ];

        $this->db->insert('enquiry', $data);

        return $this->_json_response([
            'status'  => 'success',
            'message' => 'Subscribed! Watch your inbox for our next newsletter.'
        ]);
    }

    private function _json_response($data, $status_code = 200) {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }
}
