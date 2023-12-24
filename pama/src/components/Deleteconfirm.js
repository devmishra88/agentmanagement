import React from "react";
import { useSelector, useDispatch } from "react-redux";
import { confirmDelete } from "../slices/CommonSlice";

import {
  Grid,
  Typography,
  Box,
  Drawer,
  List,
  Divider,
  ListItem,
  Button,
} from "@mui/material";

function Deleteconfirm() {
  const dispatch = useDispatch();
  const { deletepopupposition, deletepopupstatus } = useSelector(
    (state) => state.common
  );

  const DeleteContent = () => (
    <Box sx={{ width: `100%` }} role="presentation">
      <Grid item xs={12}>
        <Typography
          component={`p`}
          sx={{
            pt: 2,
            pl: 2,
            pr: 2,
            color: `#0000008a`,
          }}
        >
          Are you sure you want to delete?
        </Typography>
        <List>
          <ListItem
            sx={{
              p: 0,
              m: 0,
            }}
          >
            <Button
              sx={{
                p: 0,
                m: 0,
                mb: 1,
                color: `#2196f3`,
                fontSize: 15,
              }}
              size="small"
              onClick={() => {
                dispatch(
                  confirmDelete({
                    deletepopupposition: `bottom`,
                    candelete:true,
                    deletepopupstatus: false,
                  })
                );
              }}
            >
              Yes
            </Button>
          </ListItem>
          <Divider />
          <ListItem
            sx={{
              p: 0,
              m: 0,
            }}
          >
            <Button
              sx={{
                p: 0,
                m: 0,
                mb: 1,
                color: `#ff3b30`,
                fontSize: 15,
              }}
              color="secondary"
              size="small"
              onClick={() => {
                dispatch(
                  confirmDelete({
                    deletepopupposition: `bottom`,
                    deletepopupstatus: false,
                    candelete:false,
                    deletionrecordid:``,
                  })
                );
              }}
            >
              No
            </Button>
          </ListItem>
        </List>
      </Grid>
    </Box>
  );

  return (
    <>
      <Drawer
        anchor={deletepopupposition}
        open={deletepopupstatus}
        onClose={() =>
          dispatch(
            confirmDelete({
              deletepopupposition: `bottom`,
              deletepopupstatus: false,
              candelete:false,
              deletionrecordid:``,
            })
          )
        }
      >
        {DeleteContent()}
      </Drawer>
    </>
  );
}

export default Deleteconfirm;
