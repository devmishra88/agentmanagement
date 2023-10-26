import { useEffect } from "react";
import { useFormik } from "formik";
import * as Yup from "yup";
import FormikControl from "../FormikControl";
import { secureRequest, objectToFormData } from "../utils/axios-utils";
import { useMutation } from "react-query";
import { useDispatch } from "react-redux";
import { handleToastMsg, toggleLoader } from "../slices/CommonSlice";

import {
  Grid,
  Box,
  Container,
} from "@mui/material";

import { AppHeader, AppFooter } from "../components";

function AddArea() {
  const dispatch = useDispatch();

  const validationSchema = Yup.object({
    name: Yup.string().required("Name is Required"),
  });

  const initialValues = {
    name: "",
    remarks: "",
    status: false,
  };

  const mutation = useMutation((userData) =>
    secureRequest({ url: `/area.php`, method: `POST`, data: userData }).then(
      (response) => response
    )
  );

  const formik = useFormik({
    initialValues,
    validationSchema,
    onSubmit: (values) => {
      const { status } = values;
      const reqObject = { Mode: "AddArea", status: Number(status), ...values };
      const formData = objectToFormData(reqObject);

      mutation.mutate(formData, {
        onSuccess: (res) => {
          const { data } = res;
          const { success, msg } = data;

          dispatch(handleToastMsg({ toaststatus: true, toastmsg: msg }));

          // if (!success) {
          //   dispatch(handleToastMsg({ toaststatus: true, toastmsg: msg }));
          // }
        },
        onError: (res) => {
          console.log("Error=>", res);
        },
      });
    },
  });

  const { isLoading /*, isError, isSuccess, data: resdata*/ } = mutation;

  useEffect(() => {
    dispatch(toggleLoader({ loaderstatus: isLoading }));
  }, [isLoading]);

  return (
    <>
      <AppHeader>Add Area</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
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
                type="textarea"
                label="Remarks"
                name="remarks"
                value={formik.values.remarks}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.touched.remarks && Boolean(formik.errors.remarks)}
                helperText={formik.touched.remarks && formik.errors.remarks}
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
        </Grid>
      </Container>
      <AppFooter onClick={formik.handleSubmit} />
    </>
  );
}

export default AddArea;
