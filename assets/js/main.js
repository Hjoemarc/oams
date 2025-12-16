$(document).ready(function () {
//   $(document).on("submit", "#login-form", function (e) { 
//       e.preventDefault();
//       const $form = $(this);
//       if ($form.data("isSubmitted")) return;
//       $form.data("isSubmitted", true);
     
//       const formData = new FormData(this);
//       const $btn = $form.find("button[type='submit']");
//       $btn.prop("disabled", true);
//       $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Sending Verification Code...');
      
//       $.ajax({
//           url: base_url + "authentication/action.php?action=login",
//           type: "POST",
//           data: formData,
//           processData: false,
//           contentType: false,
//           dataType: "json",
//           success: function (response) {
//               if (response.status === 1) {
//                   Swal.fire({
//                       title: "Success!",
//                       text: response.message,
//                       icon: "success",
//                       toast: true,
//                       position: "top-end",
//                       timer: 3000,
//                       showConfirmButton: false,
//                   }).then(() => {
//                     window.location.href = 'loginVerification.php';
//                   });
//               } else {
//                   Swal.fire({
//                       title: "Error",
//                       text: response.message,
//                       icon: "error",
//                       toast: true,
//                       position: "top-end",
//                       timer: 3000,
//                       showConfirmButton: false,
//                   });
//               }
//           },
//           error: function (jqXHR, textStatus, err) {
//               console.error("AJAX error:", textStatus, err);
//               Swal.fire({
//                   title: "Connection Error",
//                   text: textStatus + ": " + err,
//                   icon: "error",
//                   toast: true,
//                   position: "top-end",
//                   timer: 3000,
//                   showConfirmButton: false,
//               });
//           },
//           complete: function () {
//               $form.data("isSubmitted", false);
//               $btn.prop("disabled", false).html('Create Account');
//           }
//       });
//   });
//   this is for offline login

//   
    const showError = (message) => {
        Swal.fire({
            title: "Error!",
            text: message,
            icon: "error",
            toast: true,
            position: "top-end",
            timer: 3000,
            showConfirmButton: false,
        });
    };
    $("body").on("submit", "#login-form", function (e) {
        e.preventDefault();
        const $this = $(this);
        const username = $this.find("input[name=username]").val();
        const password = $this.find("input[name=password]").val();

        if (!$this.hasClass("processing")) {
            $this.addClass("processing");

            const $submitBtn = $this.find("button[type='submit']");
            const originalBtnText = $submitBtn.text();

            $.ajax({
                url: base_url + "/authentication/action.php?action=login",
                method: "POST",
                dataType: "json",
                data: {
                    username: username,
                    password: password,
                },
                beforeSend: function () {
                    $submitBtn
                        .prop("disabled", true)
                        .html(
                            `<span class="spinner-border spinner-border-sm"></span> Processing...`
                        );
                },
                success: function (response) {
                    if (response.status == 1) {
                        $this.find("button").text("Login Success!");
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            toast: true,
                            position: "top-end",
                            timer: 3000,
                            showConfirmButton: false,
                        }).then(() => {
                            window.location.href =
                                base_url + response.redirect_url || base_url + "src/";
                        });
                        $this.removeClass("processing");
                    } else {
                        showError(response.message); // Now this will work
                        $this.removeClass("processing");
                        $this.find("button").text("Login");
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error:", textStatus, errorThrown);
                    if (jqXHR) {
                        // You could also use showError here
                        showError("Incorrect Email/Username or Password");
                        $this.removeClass("processing");
                    }
                    $this.removeClass("processing");
                },
                complete: function () {
                    $submitBtn.prop("disabled", false).html(originalBtnText);
                    $this.removeClass("processing");
                },
            });
        }
    });
    $(document).on("submit", "#changePassword-verification-form", function (e) { 
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        
        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Sending Verification Code...');
        
        $.ajax({
            url: base_url + "authentication/action.php?action=changePassword_verification_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = 'changePassword.php';
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Account');
            }
        });
    });
    
    $(document).on("submit", "#changePassword-form", function (e) { 
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        
        // Validate passwords match
        const password = $form.find('[name="password"]').val();
        const cpassword = $form.find('[name="cpassword"]').val();
        
        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Sending Verification Code...');
        
        $.ajax({
            url: base_url + "authentication/action.php?action=changePassword_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Processing');
            }
        });
    });
    $(document).on("submit", "#forget-account-form", function (e) { 
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        
        // Validate passwords match
        const password = $form.find('[name="password"]').val();
        const cpassword = $form.find('[name="cpassword"]').val();
        
        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Sending Verification Code...');
        
        $.ajax({
            url: base_url + "authentication/action.php?action=forget_account_form",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = 'veirificationPass.php';
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Processing');
            }
        });
    });

//   ACCOUNT MANAGEMENT ================================================
    $(document).on("submit", "#register-form", function (e) { 
        e.preventDefault();
        const $form = $(this);
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        
        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Creating Account...');
        
        $.ajax({
            url: base_url + "authentication/action.php?action=register_form", // FIXED: removed # prefix
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        $form[0].reset(); 
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("AJAX error:", textStatus, err);
                console.error("Full error response:", jqXHR.responseText); // Add this for more details
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Create Account'); // Fixed button text
            }
        });
    });

    $(document).on("submit", "#rejection-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=rejection_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#approval-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=approval_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });

// DEPARTMENTS AND PROGRAMS ========================================= 
    $(document).on("submit", "#department-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=department_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#edit-department", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      // Validate passwords match
      const password = $form.find('[name="password"]').val();
      const cpassword = $form.find('[name="cpassword"]').val();
      
      if (password !== cpassword) {
          Swal.fire({
              title: "Error",
              text: "Passwords do not match!",
              icon: "error",
              toast: true,
              position: "top-end",
              timer: 1000,
              showConfirmButton: false
          });
          $form.data("isSubmitted", false);
          return;
      }
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_department",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-department", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      // Validate passwords match
      const password = $form.find('[name="password"]').val();
      const cpassword = $form.find('[name="cpassword"]').val();
      
      if (password !== cpassword) {
          Swal.fire({
              title: "Error",
              text: "Passwords do not match!",
              icon: "error",
              toast: true,
              position: "top-end",
              timer: 1000,
              showConfirmButton: false
          });
          $form.data("isSubmitted", false);
          return;
      }
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_department",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    
    $(document).on("submit", "#programs-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=programs_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#update-programs", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=update_programs",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-program", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_program",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });

// Accreditation Framework ========================================= 
    $(document).on("submit", "#area-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=area_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#edit-area-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_area_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-area-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_area_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    }); 

    $(document).on("submit", "#indicator-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=indicator_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    }); 
    $(document).on("submit", "#edit-indicator-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_indicator_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-indicator-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_indicator_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });  

// Accreditation Cycles  =================================================
    $(document).on("submit", "#cycle-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=cycle_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#edit-cycle-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_cycle_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-cycle-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_cycle_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on('click', '#getCycleId', function () {
        const cycle_id = $(this).data('id');

        document.getElementById('activateCycleId').value = cycle_id;
        document.getElementById('closeCycleId').value = cycle_id;
    });
    $(document).on("submit", "#activate-cycle-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=activate_cycle_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#close-cycle-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=close_cycle_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });

// Document Management ==================================================
    $(document).on("submit", "#document-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=document_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#edit-document-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_document_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Update');
          }
      });
    });
    $(document).on("submit", "#delete-document-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_document_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });  
    $(document).on("submit", "#approve-document-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=approve_document_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 2000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#revise-document-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=revise_document_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 2000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on('click', '#get_docuemntAndreview_id', function () {
        const document_id = $(this).data('document-id');
        const review_id = $(this).data('review-id');

        document.getElementById('approveDocumentId').value = document_id;
        document.getElementById('approvereviewId').value = review_id;
        document.getElementById('reviseDocumentId').value = document_id;
        document.getElementById('revisereviewId').value = review_id;
    });

// Compliance Tracking ===================================================
    $(document).on("submit", "#add-compliance-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=add_compliance_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#edit-compliance-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=edit_compliance_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Compliance updated successfully!.",
                  icon: "success",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    }); 
    $(document).on("submit", "#delete-compliance-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=get_related_documents",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    }); 

// Notifications ===================================================    
    $(document).on("submit", "#notification-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=notification_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    });
    $(document).on("submit", "#delete-notification-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_notification_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Create Account');
          }
      });
    }); 

// Assignments ===========================================================
    $(document).on("submit", "#changePass_form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=changePass_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('change password');
          }
      });
    });
    $(document).on("submit", "#assignment-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=assignment_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Assign faculty');
          }
      });
    });
    $(document).on("submit", "#edit-assignment-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=update_assignment",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('EDIT');
          }
      });
    }); 
    $(document).on("submit", "#delete-assignment-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=delete_assignment_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 1000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 1000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('DELETE');
          }
      });
    });  
    $(document).on('click', '#getDocumentId', function () {
        const assignment_id = $(this).data('assignment-id');
        const document_id = $(this).data('document-id');
        const faculty_id = $(this).data('faculty-id');
        
        document.getElementById('get_assignment_id').value = assignment_id;
        document.getElementById('get_document_id').value = document_id; 
        document.getElementById('get_faculty_id').value = faculty_id; 
    });
    $(document).on("submit", "#assign-reviewer-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=assign_reviewer_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 2000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 2000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Assign Reviewer');
          }
      });
    }); 

// user_profile =======================================================
    $(document).on("submit", "#profile-update", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=profile_update",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Profile Updated');
          }
      });
    });

// Parameter form ----------------------------------------------------------
     $(document).on("submit", "#parameter-form", function (e) { 
      e.preventDefault();
      const $form = $(this);
      if ($form.data("isSubmitted")) return;
      $form.data("isSubmitted", true);
      
      const formData = new FormData(this);
      const $btn = $form.find("button[type='submit']");
      $btn.prop("disabled", true);
      $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
      
      $.ajax({
          url: base_url + "authentication/action.php?action=parameter_form",
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
          success: function (response) {
              if (response.status === 1) {
                  Swal.fire({
                      title: "Success!",
                      text: response.message,
                      icon: "success",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  }).then(() => {
                      $form[0].reset(); // Reset form on success
                  });
              } else {
                  Swal.fire({
                      title: "Error",
                      text: response.message,
                      icon: "error",
                      toast: true,
                      position: "top-end",
                      timer: 3000,
                      showConfirmButton: false,
                  });
              }
          },
          error: function (jqXHR, textStatus, err) {
              console.error("AJAX error:", textStatus, err);
              Swal.fire({
                  title: "Connection Error",
                  text: "Please check your connection and try again.",
                  icon: "error",
                  toast: true,
                  position: "top-end",
                  timer: 3000,
                  showConfirmButton: false,
              });
          },
          complete: function () {
              $form.data("isSubmitted", false);
              $btn.prop("disabled", false).html('Add parameter');
          }
      });
    });

// Documment feedbacks ==================================================
    $(document).on('click', '.getDocumentId', function () {
        const document_id = $(this).data('id');
        $('#document_id_value').val(document_id);
    });
    $(document).on("submit", "#document-feedback", function (e) { 
        e.preventDefault();
        const $form = $(this);
        
        // Debug: Check document ID
        const document_id = $('#document_id_value').val();
        console.log("📋 Form submitted - Document ID:", document_id);
        
        if (!document_id) {
            console.error("❌ No document ID selected!");
            Swal.fire({
                title: "Error",
                text: "Please select a document first by clicking a document row",
                icon: "error",
                toast: true,
                position: "top-end",
                timer: 3000,
                showConfirmButton: false,
            });
            return;
        }
        
        if ($form.data("isSubmitted")) return;
        $form.data("isSubmitted", true);
        
        const formData = new FormData(this);
        const $btn = $form.find("button[type='submit']");
        $btn.prop("disabled", true);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
        
        // Debug: Show what's being sent
        console.log("📤 Sending form data:");
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Build the URL - CHECK THIS CAREFULLY
        const actionUrl = base_url + "/authentication/action.php?action=document_feedback";
        console.log("🌐 AJAX URL:", actionUrl);
        
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                console.log("✅ Success response:", response);
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function (jqXHR, textStatus, err) {
                console.error("❌ AJAX error details:");
                console.error("Status:", textStatus);
                console.error("Error:", err);
                console.error("jqXHR:", jqXHR);
                console.error("Response Text:", jqXHR.responseText);
                console.error("Status Code:", jqXHR.status);
                console.error("Status Text:", jqXHR.statusText);
                
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again. Error: " + textStatus,
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            },
            complete: function () {
                $form.data("isSubmitted", false);
                $btn.prop("disabled", false).html('Submit Feedback');
            }
        });
    });

});
