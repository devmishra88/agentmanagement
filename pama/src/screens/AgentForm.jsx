import { useState, useEffect } from "react";
import { useFormik } from "formik";
import * as Yup from "yup";
import FormikControl from "../FormikControl";
import { secureRequest, objectToFormData } from "../utils/axios-utils";
import { useMutation } from "react-query";
import { useDispatch } from "react-redux";
import { handleToastMsg, toggleLoader } from "../slices/CommonSlice";
import useQueryParams from "../hooks/useQueryParams";
import useSwitchRoute from "../hooks/useSwitchRoute";
import { useSingleAreaData } from "../hooks/useAreaData";

import { Container, Box, AppBar, Tabs, Tab } from "@mui/material";
import { AppHeader, AppFooter } from "../components";

import PropTypes from "prop-types";
import SwipeableViews from "react-swipeable-views";
import { useTheme } from "@mui/material/styles";

import { AccountCircleIcon, NewspaperIcon } from "../constants";

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`full-width-tabpanel-${index}`}
      aria-labelledby={`full-width-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ pt: 2 }}>{children}</Box>}
    </div>
  );
}

TabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.number.isRequired,
  value: PropTypes.number.isRequired,
};

function a11yProps(index) {
  return {
    id: `full-width-tab-${index}`,
    "aria-controls": `full-width-tabpanel-${index}`,
  };
}

function AgentForm() {
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
  } = useSingleAreaData(queryobject.id);

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
      const reqObject = {
        Mode: `${mode}Area`,
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
              switchRoute(`/areas`, true);
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
      const areaDetail = data.data.areadetail;

      if (areaDetail) {
        const { name, remark, status } = areaDetail;

        formik.setValues({
          name: name || "",
          remarks: remark || "",
          status: Number(status) === 1,
        });
      }
    }
  }, [data]);

  const theme = useTheme();

  const [activetab, setActiveTab] = useState(0);

  const handleActiveTab = (event, newValue) => {
    setActiveTab(newValue);
  };

  const handleChangeIndex = (index) => {
    setActiveTab(index);
  };

  return (
    <>
      <AppHeader spacing={0} applyshadow={false}>
        {mode} Agent
      </AppHeader>
      <form onSubmit={formik.handleSubmit} noValidate>
        <AppBar
          position="static"
          sx={{
            top: "auto",
            bottom: 0,
            background: `#F7F7F8`,
            boxShadow: `0px 2px 4px -1px rgba(0,0,0,0.2), 0px 4px 5px 0px rgba(0,0,0,0.14), 0px 1px 10px 0px rgba(0,0,0,0.12)`,
            color: `#000000`,
          }}
        >
          <Tabs
            value={activetab}
            onChange={handleActiveTab}
            textColor="inherit"
            variant="fullWidth"
            aria-label="form tabs"
          >
            <Tab
              icon={<AccountCircleIcon />}
              label="Profile"
              {...a11yProps(0)}
            />
            <Tab icon={<NewspaperIcon />} label="Paper Fee" {...a11yProps(1)} />
          </Tabs>
        </AppBar>
        <SwipeableViews
          axis={theme.direction === "rtl" ? "x-reverse" : "x"}
          index={activetab}
          onChangeIndex={handleChangeIndex}
        >
          <TabPanel value={activetab} index={0} dir={theme.direction}>
            <Container maxWidth="lg">
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
            </Container>
          </TabPanel>
          <TabPanel value={activetab} index={1} dir={theme.direction}>
            <Container maxWidth="lg">Item Two</Container>
          </TabPanel>
        </SwipeableViews>
      </form>
      <AppFooter onClick={formik.handleSubmit} />
    </>
  );
}

export default AgentForm;
