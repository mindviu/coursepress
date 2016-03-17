<?php
/**
 * Data access module.
 *
 * @package CoursePress
 */

/**
 * Helper class for working with CoursePress capabilities.
 *
 * Previously CoursePress_Capabilities
 *
 * @since 1.0.0
 */
class CoursePress_Data_Capabilities {

	public static $capabilities = array(
		'instructor' => array(
			/* General */
			'coursepress_dashboard_cap' => 1,
			'coursepress_courses_cap' => 1,
			'coursepress_instructors_cap' => 0, // DEPRECATED
			'coursepress_students_cap' => 1,
			'coursepress_assessment_cap' => 1,
			'coursepress_reports_cap' => 1,
			'coursepress_notifications_cap' => 1,
			'coursepress_discussions_cap' => 1,
			'coursepress_settings_cap' => 1,
			/* Courses */
			'coursepress_create_course_cap' => 1,
			'coursepress_update_course_cap' => 1,
			'coursepress_update_my_course_cap' => 1,
			'coursepress_update_all_courses_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_delete_course_cap' => 0,
			'coursepress_delete_my_course_cap' => 1,
			'coursepress_delete_all_courses_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_change_course_status_cap' => 0,
			'coursepress_change_my_course_status_cap' => 1,
			'coursepress_change_all_courses_status_cap' => 0, // NOT IMPLEMENTED YET
			/* Units */
			'coursepress_create_course_unit_cap' => 1,
			'coursepress_view_all_units_cap' => 0,
			'coursepress_update_course_unit_cap' => 1,
			'coursepress_update_my_course_unit_cap' => 1,
			'coursepress_update_all_courses_unit_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_delete_course_units_cap' => 1,
			'coursepress_delete_my_course_units_cap' => 1,
			'coursepress_delete_all_courses_units_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_change_course_unit_status_cap' => 1,
			'coursepress_change_my_course_unit_status_cap' => 1,
			'coursepress_change_all_courses_unit_status_cap' => 0, // NOT IMPLEMENTED YET
			/* Instructors */
			'coursepress_assign_and_assign_instructor_course_cap' => 0,
			'coursepress_assign_and_assign_instructor_my_course_cap' => 1,
			/* Classes */
			'coursepress_add_new_classes_cap' => 0,
			'coursepress_add_new_my_classes_cap' => 0,
			'coursepress_delete_classes_cap' => 0,
			'coursepress_delete_my_classes_cap' => 0,
			/* Students */
			'coursepress_invite_students_cap' => 0,
			'coursepress_invite_my_students_cap' => 1,
			'coursepress_withdraw_students_cap' => 0,
			'coursepress_withdraw_my_students_cap' => 1,
			'coursepress_add_move_students_cap' => 0,
			'coursepress_add_move_my_students_cap' => 1,
			'coursepress_add_move_my_assigned_students_cap' => 1,
			// 'coursepress_change_students_group_class_cap' => 0,
			// 'coursepress_change_my_students_group_class_cap' => 0,
			'coursepress_add_new_students_cap' => 0, // DEPRECATED
			'coursepress_send_bulk_my_students_email_cap' => 0, // DEPRECATED
			'coursepress_send_bulk_students_email_cap' => 0, // DEPRECATED
			'coursepress_delete_students_cap' => 0, // DEPRECATED
			/* Groups */
			'coursepress_settings_groups_page_cap' => 0,
			// 'coursepress_settings_shortcode_page_cap' => 0,
			/* Notifications */
			'coursepress_create_notification_cap' => 1,
			'coursepress_create_my_assigned_notification_cap' => 1,
			'coursepress_create_my_notification_cap' => 1,
			'coursepress_update_notification_cap' => 0,
			'coursepress_update_my_notification_cap' => 1,
			'coursepress_delete_notification_cap' => 0,
			'coursepress_delete_my_notification_cap' => 1,
			'coursepress_change_notification_status_cap' => 0,
			'coursepress_change_my_notification_status_cap' => 1,
			/* Discussions */
			'coursepress_create_discussion_cap' => 1,
			'coursepress_create_my_assigned_discussion_cap' => 1,
			'coursepress_create_my_discussion_cap' => 1,
			'coursepress_update_discussion_cap' => 0,
			'coursepress_update_my_discussion_cap' => 1,
			'coursepress_delete_discussion_cap' => 0,
			'coursepress_delete_my_discussion_cap' => 1,
			'coursepress_change_discussion_status_cap' => 0,
			'coursepress_change_my_discussion_status_cap' => 1,
			/* Certificates */
			'coursepress_certificates_cap' => 0,
			'coursepress_create_certificates_cap' => 0,
			'coursepress_update_certificates_cap' => 0,
			'coursepress_delete_certificates_cap' => 0,
			/* Course Categories */
			'coursepress_course_categories_manage_terms_cap' => 1,
			'coursepress_course_categories_edit_terms_cap' => 1,
			'coursepress_course_categories_delete_terms_cap' => 0,
			/* Posts and Pages */
			'edit_pages' => 0,
			'edit_published_pages' => 0,
			'edit_posts' => 0,
			'publish_pages' => 0,
			'publish_posts' => 0,
		),
	);

	public static function init() {
		add_action( 'set_user_role', array( __CLASS__, 'assign_role_capabilities' ), 10, 3 );
		add_action( 'wp_login', array( __CLASS__, 'restore_capabilities' ), 10, 2 );
	}

	/**
	 * Assign appropriate CoursePress capabilities for roles
	 *
	 * @since 1.2.3.3.
	 */
	public static function assign_role_capabilities( $user_id, $role, $old_role ) {

		if ( 'administrator' == $role ) {
			self::assign_admin_capabilities( $user_id );
		} else {

			// Remove all CoursePress capabilities
			self::remove_instructor_capabilities( $user_id );

			$instructor_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
			// If they are an instructor, give them their appropriate capabilities back
			if ( ! empty( $instructor_courses ) ) {
				self::assign_instructor_capabilities( $user_id );
			}
		}
	}

	/**
	 * Make sure the admin has required capabilities
	 *
	 * @since 1.2.3.3.
	 */
	public static function restore_capabilities( $user_login = false, $user ) {

		if ( user_can( $user, 'manage_options' ) ) {
			self::assign_admin_capabilities( $user );
			return;
		}
		if ( ! empty( CoursePress_Data_Instructor::get_course_count( $user->id ) ) ) {
			self::assign_instructor_capabilities( $user->ID );
			return;
		}
		self::remove_instructor_capabilities( $user );
	}

	public static function remove_instructor_capabilities( $user ) {
		if ( ! is_object( $user ) ) {
			$user = new WP_User( $user_id );
		}
		$capability_types = self::$capabilities['instructor'];
		foreach ( $capability_types as $key => $value ) {
			$user->remove_cap( $key );
		}
	}

	public static function assign_admin_capabilities( $user ) {
		if ( ! is_object( $user ) ) {
			$user_id = CoursePress_Helper_Utility::get_id( $user );
			$user = new WP_User( $user_id );
		}
		/**
		 * do not add_cap if user can manage_options
		 */
		if ( user_can( $user->ID, 'manage_options' ) ) {
			return;
		}
		$capability_types = self::$capabilities['instructor'];
		foreach ( $capability_types as $key => $value ) {
			$user->add_cap( $key );
		}

	}

	/**
	 * Can add course?
	 *
	 * @since 2.0.0
	 *
	 * @param integer $user_id User ID or empty.
	 *
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_course( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Create new courses
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_course_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Can the user update this course?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_update_course( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		$course_id = is_object( $course )? $course->ID : $course;
		if ( CoursePress_Data_Instructor::is_assigned_to_course( $user_id, $course_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_course_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * update my own course
		 */
		if ( self::is_course_creator( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_my_course_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can the user delete this course?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_delete_course( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * delete my own course
		 */
		if ( self::is_course_creator( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_my_course_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * delete any assigned course
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( CoursePress_Data_Instructor::is_assigned_to_course( $user_id, $course_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_course_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can the user change the course status?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_change_course_status( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * change_status any assigned course
		 */
		if ( self::is_course_instructor( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_course_status_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * change_status my own course
		 */
		if ( empty( $course ) || self::is_course_creator( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_my_course_status_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can the user create units?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_create_unit( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_course_unit_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Can the user create units in this course?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_create_course_unit( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$can_update_course = self::can_update_course( $course_id, $user_id );
		$can_create_units = self::can_create_unit( $user_id );

		return ( $can_update_course && $can_create_units ) || user_can( $user_id, 'manage_options' ) ? true : false;
	}

	/**
	 * Can the user view units?
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post/integer  $course Course data or $course_id
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_view_course_units( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * check can view every courses
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_view_all_units_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * if can edit, then , can view!
		 */
		return self::can_update_course( $course, $user_id );
	}

	/**
	 * Can the user update the units?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_update_course_unit( $course, $unit_id = '', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Update own units
		 */
		$my_unit = self::is_unit_creator( $unit_id, $user_id );
		if ( $my_unit ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_my_course_unit_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * Update any unit within assigned courses
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		$my_course = self::is_course_instructor( $course_id, $user_id );
		if ( $my_course ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_course_unit_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can the user delete the units?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_delete_course_unit( $course, $unit_id = '', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Delete own units
		 */
		$my_unit = self::is_unit_creator( $unit_id, $user_id );
		if ( $my_unit ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_my_course_units_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * Delete any unit within assigned courses
		 */
		$my_course = self::is_course_instructor( $course, $user_id );
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_my_course_units_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Can the user change the unit state?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_change_course_unit_status( $course, $unit_id = '', $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * change_status my Course unit
		 */
		$my_unit = self::is_unit_creator( $unit_id, $user_id );
		if ( $my_unit ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_status_my_course_units_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * change_status assigned
		 */
		$my_course = self::is_course_instructor( $course, $user_id );
		if ( empty( $unit_id ) || $my_course ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_course_unit_status_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can the user assign a course instructor?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course data.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_assign_course_instructor( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * check can assign instructor every courses
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_assign_and_assign_instructor_course_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * check instructor
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( self::is_course_instructor( $course_id, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_assign_and_assign_instructor_my_course_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;

	}

	/**
	 * Can the user invite students?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function can_assign_course_student( $course_id, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$my_course = self::is_course_instructor( $course_id, $user_id );

		return ( $my_course && user_can( $user_id, 'coursepress_invite_my_students_cap' ) ) || user_can( $user_id, 'coursepress_invite_students_cap' ) || user_can( $user_id, 'manage_options' ) ? true : false;
	}

	/**
	 * Can add student
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course data.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_course_student( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Add students to any course
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_add_move_students_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * Add students to own courses
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( self::is_course_creator( $course, $user_id ) ) {
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_add_move_my_students_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * Add students to assigned courses
		 */
		if ( CoursePress_Data_Instructor::is_assigned_to_course( $user_id, $course_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_add_move_my_assigned_students_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Can withdraw student
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course data.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_withdraw_course_student( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * withdraw students to any course
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_withdraw_students_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * withdraw students to own courses
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( self::is_course_creator( $course, $user_id ) ) {
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_withdraw_my_students_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * NOTIFICATIONS
	 *
	 */

	/**
	 * Can withdraw student
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course data.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_notification_to_all( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Create new notifications
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_notification_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Can add notification
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course Course data or course ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_notification( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Create new notifications
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_notification_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * Create new notifications for own courses
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( self::is_course_creator( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_notification_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * Create new notifications for assigned courses
		 */
		if ( self::is_course_instructor( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_assigned_notification_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can update notification
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $notification notification data or notification ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_update_notification( $notification, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Update every notification
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_notification_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * Update own notifications
		 */
		$notification_id = is_object( $notification )? $notification->ID : $notification;
		if ( self::is_notification_creator( $notification, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_my_notification_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can delete notification
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $notification notification data or notification ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_delete_notification( $notification, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * delete every notification
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_notification_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * delete own notifications
		 */
		$notification_id = is_object( $notification )? $notification->ID : $notification;
		if ( self::is_notification_creator( $notification, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_my_notification_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can change_status notification
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $notification notification data or notification ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_change_status_notification( $notification, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * change_status every notification
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_notification_status_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * change_status own notifications
		 */
		$notification_id = is_object( $notification )? $notification->ID : $notification;
		if ( self::is_notification_creator( $notification, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_my_notification_status_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * DISCUSSIONS
	 *
	 */

	/**
	 * Can withdraw student
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course data.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_discussion_to_all( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Create new discussions
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_discussion_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Can add discussion
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course Course data or course ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_add_discussion( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Create new discussions
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_discussion_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * Create new discussions for own courses
		 */
		$course_id = is_object( $course )? $course->ID : $course;
		if ( self::is_course_creator( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_discussion_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		/**
		 * Create new discussions for assigned courses
		 */
		if ( self::is_course_instructor( $course, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_assigned_discussion_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can update discussion
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $discussion discussion data or discussion ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_update_discussion( $discussion, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * Update every discussion
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_discussion_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * Update own discussions
		 */
		$discussion_id = is_object( $discussion )? $discussion->ID : $discussion;
		if ( self::is_discussion_creator( $discussion, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_update_my_discussion_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can delete discussion
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $discussion discussion data or discussion ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_delete_discussion( $discussion, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * delete every discussion
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_discussion_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * delete own discussions
		 */
		$discussion_id = is_object( $discussion )? $discussion->ID : $discussion;
		if ( self::is_discussion_creator( $discussion, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_delete_my_discussion_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Can change_status discussion
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $discussion discussion data or discussion ID.
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function can_change_status_discussion( $discussion, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		/**
		 * change_status every discussion
		 */
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_discussion_status_cap' );
		if ( user_can( $user_id, $capability ) ) {
			return true;
		}
		/**
		 * change_status own discussions
		 */
		$discussion_id = is_object( $discussion )? $discussion->ID : $discussion;
		if ( self::is_discussion_creator( $discussion, $user_id ) ) {
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			$capability = apply_filters( 'coursepress_capabilities', 'coursepress_change_my_discussion_status_cap' );
			if ( user_can( $user_id, $capability ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * HELPERS
	 */

	/**
	 * Is the user an instructor of this course?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_course_instructor( $course, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$course_id = is_object( $course )? $course->ID : $course;
		$instructor_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
		return in_array( $course_id, $instructor_courses );
	}

	/**
	 * Is the user the course author?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_course_creator( $course, $user_id = '' ) {
		return self::is_creator(
			$course,
			CoursePress_Data_Course::get_post_type_name(),
			$user_id
		);
	}

	/**
	 * Is the user the unit author?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_unit_creator( $unit = '', $user_id = '' ) {
		return self::is_creator(
			$unit,
			CoursePress_Data_Unit::get_post_type_name(),
			$user_id
		);
	}

	/**
	 * Is the user the notification author?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course Course data or course ID.
	 * @param integer $user_id user ID, can be empty
	 *
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function is_notification_creator( $notification, $user_id = '' ) {
		return self::is_creator(
			$notification,
			CoursePress_Data_Notification::get_post_type_name(),
			$user_id
		);
	}

	/**
	 * Is the user the discussion author?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course Course data or course ID.
	 * @param integer $user_id user ID, can be empty
	 *
	 * @return boolean Can or can't? - this is a question.
	 */
	public static function is_discussion_creator( $discussion, $user_id = '' ) {
		return self::is_creator(
			$discussion,
			CoursePress_Data_Discussion::get_post_type_name(),
			$user_id
		);
	}

	/**
	 * Is the user the discussion author?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post/integer $course Course data or course ID.
	 * @param integer $user_id user ID, can be empty
	 *
	 * @return boolean Can or can't? - this is a question.
	 */
	private static function is_creator( $post, $post_type, $user_id = '' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$post_id = is_object( $post )? $post->ID : $post;
		if ( empty( $post_id ) ) {
			return false;
		} else {
			/**
			 * check post type
			 */
			if ( get_post_type( $post ) != $post_type ) {
				return false;
			}
			/**
			 * check author
			 */
			return get_post_field( 'post_author', $post_id ) == $user_id;
		}
	}

	public static function grant_private_caps( $user_id ) {
		$user = new WP_User( $user_id );
		/**
		 * do not add_cap if user can manage_options
		 */
		if ( user_can( $user_id, 'manage_options' ) ) {
			return;
		}
		$capability_types = array( 'course', 'unit', 'module', 'module_response', 'notification', 'discussion' );
		foreach ( $capability_types as $capability_type ) {
			$user->add_cap( "read_private_{$capability_type}s" );
		}
	}

	public static function drop_private_caps( $user_id = '', $role = '' ) {

		if ( empty( $user_id ) && empty( $role ) ) {
			return;
		}
		/**
		 * do not add_cap if user can manage_options
		 */
		if ( user_can( $user_id, 'manage_options' ) ) {
			return;
		}

		$user = false;
		if ( ! empty( $user_id ) ) {
			$user = new WP_User( $user_id );
		}

		$capability_types = array( 'course', 'unit', 'module', 'module_response', 'notification', 'discussion' );

		foreach ( $capability_types as $capability_type ) {
			if ( ! empty( $user ) ) {
				$user->remove_cap( "read_private_{$capability_type}s" );
			}
			if ( ! empty( $role ) ) {
				$role->remove_cap( "read_private_{$capability_type}s" );
			}
		}
	}

	public static function assign_instructor_capabilities( $user ) {

		$user_id = CoursePress_Helper_Utility::get_id( $user );
		/**
		 * do not add_cap if user can manage_options
		 */
		if ( user_can( $user_id, 'manage_options' ) ) {
			return;
		}
		// The default capabilities for an instructor
		$instructor_capabilities = self::get_instructor_capabilities();

		$role = new WP_User( $user_id );

		$global_option = ! is_multisite();
		update_user_option( $user_id, 'role_ins', 'instructor', $global_option );

		$role->add_cap( 'can_edit_posts' );
		$role->add_cap( 'read' );
		$role->add_cap( 'upload_files' );

		foreach ( $instructor_capabilities as $capability_name => $capability_status ) {
			if ( $capability_status ) {
				$role->add_cap( $capability_name );
			} else {
				$role->remove_cap( $capability_name );
			}
		}
	}

	public static function drop_instructor_capabilities( $user ) {

		$user_id = CoursePress_Helper_Utility::get_id( $user );

		if ( user_can( $user_id, 'manage_options' ) ) {
			return;
		}

		$role = new WP_User( $user_id );

		$global_option = ! is_multisite();
		delete_user_option( $user_id, 'role_ins', $global_option );
		// Legacy
		delete_user_meta( $user_id, 'role_ins', 'instructor' );

		$role->remove_cap( 'can_edit_posts' );
		$role->remove_cap( 'read' );
		$role->remove_cap( 'upload_files' );

		$capabilities = array_keys( self::$capabilities['instructor'] );
		foreach ( $capabilities as $cap ) {
			$role->remove_cap( $cap );
		}

		self::grant_private_caps( $user_id );
	}

	// Add new roles and user capabilities
	public static function add_user_roles_and_caps() {
		/* ---------------------- Add initial capabilities for the admins */
		$role = get_role( 'administrator' );
		$role->add_cap( 'read' );

		// Add ALL instructor capabilities
		$admin_capabilities = array_keys( self::$capabilities['instructor'] );
		foreach ( $admin_capabilities as $cap ) {
			$role->add_cap( $cap );
		}

		self::drop_private_caps( '', $role );
	}

	public static function get_instructor_capabilities() {
		$default_capabilities = array_keys( CoursePress_Data_Capabilities::$capabilities['instructor'], 1 );
		$instructor_capabilities = CoursePress_Core::get_setting( 'instructor/capabilities' );

		if ( empty( $instructor_capabilities ) ) {
			$instructor_capabilities = array();
			foreach ( $default_capabilities as $cap ) {
				$instructor_capabilities[ $cap ] = true;
			}
		}

		return $instructor_capabilities;
	}
}
