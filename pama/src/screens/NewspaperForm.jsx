import { useEffect } from "react";
import { useFormik } from "formik";
import * as Yup from "yup";
import FormikControl from "../FormikControl";
import { secureRequest, objectToFormData } from "../utils/axios-utils";
import { useMutation } from "react-query";
import { useDispatch } from "react-redux";
import { handleToastMsg, toggleLoader } from "../slices/CommonSlice";
import useQueryParams from "../hooks/useQueryParams";
import useSwitchRoute from "../hooks/useSwitchRoute";
import { useSingleNewspaperData } from "../hooks/useNewspaperData";

import { Grid, Box, Container } from "@mui/material";

import { AppHeader, AppFooter } from "../components";

function NewspaperForm() {
  const queryParams = useQueryParams();
  const switchRoute = useSwitchRoute();
  const dispatch = useDispatch();

  const excludedKeys = [];
  const getVars = queryParams(excludedKeys);

  const { querystring, queryobject } = getVars;

  const editData = {};

  let mode = "Add";
  if (queryobject.mode === `edit`) {
    mode = "Edit";
  }

  const {
    isLoading: isEditLoading,
    data,
    dataUpdatedAt,
    isError,
    error,
  } = useSingleNewspaperData(queryobject.id);

  const validationSchema = Yup.object({
    name: Yup.string().required("Name is Required"),
    // aircharge: Yup.string().required("Air charge is Required"),
    aircharge: Yup.number()
    .required("Air charge is Required")
    .integer("Air charge must be an integer")
    .min(0, "Air charge must be greater than or equal to 0")
  });

  const initialValues = {
    name: "",
    aircharge:"",
    status: false,
  };

  const mutation = useMutation((userData) =>
    secureRequest({ url: `/newspaper.php`, method: `POST`, data: userData }).then(
      (response) => response
    )
  );

  const formik = useFormik({
    initialValues,
    validationSchema,
    onSubmit: (values) => {
      const { status } = values;
      const reqObject = {
        Mode: `${mode}Newspaper`,
        status: Number(status),
        ...values,
        recordid: queryobject.id ?? "",
      };
      const formData = objectToFormData(reqObject);

      mutation.mutate(formData, {
        onSuccess: (res) => {
          const { data } = res;
          const { success, msg } = data;

          dispatch(handleToastMsg({ toaststatus: true, toastmsg: msg }));

          if (success) {
            if (queryobject.mode === `edit`) {
              switchRoute(`/newspapers`, true);
            } else {
              formik.resetForm();
            }
          }
        },
        onError: (res) => {
          console.log("Error=>", res);
        },
      });
    },
  });

  const { isLoading, /*isError,*/ isSuccess, data: resdata } = mutation;

  useEffect(() => {
    dispatch(toggleLoader({ loaderstatus: isLoading }));
  }, [isLoading]);

  useEffect(() => {
    if (data?.data?.success) {
      const detail = data?.data?.detail;

      if (detail) {
        const { name, aircharge, remark, status } = detail;

        formik.setValues({
          name: name || "",
          aircharge: aircharge || "",
          status: Number(status) === 1,
        });
      }
    }
  }, [data]);

  return (
    <>
      <AppHeader>{mode} Newspaper</AppHeader>
      <Container maxWidth="lg">
        <Box mt={1} spacing={1}>
          <Box>
            <form onSubmit={formik.handleSubmit} noValidate>
              <FormikControl
                control="input"
                type="text"
                label="Name"
                name="name"
                value={formik.values.name}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.touched.name && Boolean(formik.errors.name)}
                helperText={formik.touched.name && formik.errors.name}
                required
              />
              <FormikControl
                control="input"
                type="number"
                label="Air Charge"
                name="aircharge"
                value={formik.values.aircharge}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.touched.aircharge && Boolean(formik.errors.aircharge)}
                helperText={formik.touched.aircharge && formik.errors.aircharge}
                required
              />
              <FormikControl
                control="toggle"
                label="Status"
                name="status"
                checked={formik.values.status}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
              />
              <Grid
                container
                sx={{
                  display: `flex`,
                  justifyContent: `space-between`,
                  textAlign: `center`,
                }}
              ></Grid>
            </form>
          </Box>
        </Box>
      </Container>
      <AppFooter onClick={formik.handleSubmit} />
    </>
  );
}

export default NewspaperForm;
