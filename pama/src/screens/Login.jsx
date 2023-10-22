import { useEffect } from "react";
import { useFormik } from "formik";
import * as Yup from "yup";
import FormikControl from "../FormikControl";
import { request, objectToFormData } from "../utils/axios-utils";
import { Copyright } from "../components";
import { useMutation } from "react-query";
import { useSelector, useDispatch } from "react-redux";
import { handleToastMsg, toggleLoader } from "../slices/CommonSlice";
import { setToken } from "../slices/authSlice";

import {
  Avatar,
  Button,
  CssBaseline,
  Link,
  Grid,
  Box,
  Typography,
  Container,
} from "@mui/material";

import { LockOutlinedIcon } from "../constants";

export default function SignIn() {

  const dispatch = useDispatch();

  const validationSchema = Yup.object({
    phone: Yup.string()
      .matches(/^[0-9]{10}$/, "Invalid phone number")
      .required("Phone number is required"),
    password: Yup.string().required("Password is required"),
  });

  const initialValues = {
    phone: "",
    password: "",
  };

  const mutation = useMutation((userData) =>
    request({ url: `/applogin.php`, method: `POST`, data: userData }).then(
      (response) => response
    )
  );

  const formik = useFormik({
    initialValues,
    validationSchema,
    onSubmit: (values) => {
      const reqObject = { Mode: "AppLogin" /*, logintype: 0*/, ...values };
      const formData = objectToFormData(reqObject);

      mutation.mutate(formData, {
        onSuccess: (res) => {
          const { data } = res;
          const { success, msg, accesstoken, authtoken, clientdetail } = data;

          if (!success) {
            dispatch(handleToastMsg({ toaststatus: true, toastmsg: msg }));
          } else {
            dispatch(
              setToken({
                accesstoken: accesstoken,
                authtoken: authtoken,
                clientdetail: clientdetail,
              })
            );
          }
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
    <Container component="main">
      <CssBaseline />
      <Box
        sx={{
          marginTop: 8,
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
        }}
      >
        <Avatar sx={{ m: 1, bgcolor: "primary.main" }}>
          <LockOutlinedIcon />
        </Avatar>
        <Typography component="h1" variant="h5">
          Sign in
        </Typography>
        <form onSubmit={formik.handleSubmit} noValidate>
          <FormikControl
            control="input"
            type="number"
            label="Phone Number"
            name="phone"
            maxLength={10}
            value={formik.values.phone}
            onChange={formik.handleChange}
            onBlur={formik.handleBlur}
            error={formik.touched.phone && Boolean(formik.errors.phone)}
            helperText={formik.touched.phone && formik.errors.phone}
          />
          <FormikControl
            control="input"
            type="password"
            label="Password"
            name="password"
            value={formik.values.password}
            onChange={formik.handleChange}
            onBlur={formik.handleBlur}
            error={formik.touched.password && Boolean(formik.errors.password)}
            helperText={formik.touched.password && formik.errors.password}
          />
          <Button
            type="submit"
            fullWidth
            variant="contained"
            sx={{ mt: 3, mb: 2 }}
          >
            Sign In
          </Button>
          <Grid
            container
            sx={{
              display: `flex`,
              justifyContent: `space-between`,
              textAlign: `center`,
            }}
          >
            {/* <Grid item xs>
              <Link href="/forgot" variant="body2">
                Forgot password?
              </Link>
            </Grid> */}
          </Grid>
        </form>
      </Box>
      <Copyright sx={{ mt: 8, mb: 4 }} />
    </Container>
  );
}
